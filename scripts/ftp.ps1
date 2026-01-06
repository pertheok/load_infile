function Import-DotEnv {
    param (
        [Parameter(Mandatory)]
        [string] $Path
    )

    if (-not (Test-Path $Path)) {
        throw "Env file not found: $Path"
    }

    Get-Content $Path | ForEach-Object {
        $_ = $_.Trim()

        if ($_ -and -not $_.StartsWith('#')) {
            $name, $value = $_ -split '=', 2
            Set-Variable -Name $name -Value $value -Scope Script
        }
    }
}

Import-DotEnv "C:\laragon\www\CMOS-connect\config\.env"

# Load WinSCP .NET assembly
Add-Type -Path $WINSCP_DLL

# ----- OPTIONS -----

# Define the directories - remote will be mirrored locally
$remotePath = $REMOTE_PATH
$localPath = $LOCAL_PATH

# Locations of the helper scripts used here

# Market messages
$marketMessageWriteScriptPath = $MARKET_MESSAGE_SCRIPT
$marketMessageWriteScriptOutputLogPath = $MARKET_MESSAGE_OUT_LOG
$marketMessageWriteScriptErrorLogPath = $MARKET_MESSAGE_ERR_LOG

# Session log is stored here
$logFile = $SESSION_LOG

# Array containing the substrings to search for in the $downloadedItems array
$dataTypes = $DATA_TYPES -split ','

# Set up session options
$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol = [WinSCP.Protocol]::Ftp
    HostName = $FTP_HOST
    UserName = $FTP_USER
    FtpSecure = [WinSCP.FtpSecure]::Explicit
    TlsClientCertificatePath = $FTP_CERT
    PrivateKeyPassphrase = $FTP_CERT_PASS
}

# Set up additional session options
$sessionOptions.AddRawSettings("SshNoUserAuth", "1")
$sessionOptions.AddRawSettings("ConsiderDST", "0")
$sessionOptions.AddRawSettings("FtpHost", "0")

# ----- HELPER FUNCTIONS -----

# Synchronise the local and remote directories, does not remove the differences from the local
function Sync-Folders
{
    param(
        [WinSCP.SessionOptions] $sessionOpts,
        [string] $localPath,
        [string] $remotePath
    )

    # Initialise the session object
    $session = New-Object WinSCP.Session
    
    # Connect
    $session.Open($sessionOptions)
    
    # Synchronise directories
    $sync = $session.SynchronizeDirectories(
        [WinSCP.SynchronizationMode]::Local,
        $localPath,
        $remotePath,
        $False, # Prevents removal of files from the local directory if they don't appear in the remote
        $True,
        [WinSCP.SynchronizationCriteria]::None, # Compares only the filepaths - required due to sync issues
        $null
    )

    # Save filepaths of the downloaded items as a variable
    $downloads = $sync.Downloads.Destination

    # Order the file paths in ascending order
    $downloadsSorted = $downloads | Sort-Object

    # Close the session
    $session.Dispose()

    # Return the collection
    return $downloadsSorted
}

# Get the file information, such as its parent directory, its filename and what kind of data from the $dataTypes array it's meant to contain
function Get-DirectoryAndFilename
{
    param(
        [string[]] $filepaths
    )

    $result = @()

    foreach ($path in $filepaths)
    {
        $directory = Split-Path -Path $path -Parent
        $filename = Split-Path -Path $path -Leaf
        $dataType = $null
        
        foreach ($type in $dataTypes)
        {
            if ($filename -match $type)
            {
                $dataType = $type
                break
            }
        }

        $object = [PSCustomObject]@{
            Directory = $directory
            Filename = $filename
            DataType = $dataType
        }

        $result += $object
    }

    return $result
}

# ----- MAIN SCRIPT -----

"[$((Get-Date).ToString())] -----SCRIPT EXECUTION STARTED-----" | Out-File -FilePath $logFile -Append

# Synchronise the folders and save what was downloaded as a variable
$downloadedItems = Sync-Folders -sessionOpts $sessionOptions -localPath $localPath -remotePath $remotePath

# Check if any files were downloaded
if ($downloadedItems.Count -gt 0)
{
    # List the downloads
    "[$((Get-Date).ToString())] -----DOWNLOADS-----" | Out-File -FilePath $logFile -Append

    foreach ($item in $downloadedItems)
    {
        "[$((Get-Date).ToString())] File Downloaded: $item" | Out-File -FilePath $logFile -Append
    }

    "[$((Get-Date).ToString())] -----FILES TO PROCESS-----" | Out-File -FilePath $logFile -Append

    "[$((Get-Date).ToString())] -----MDS DATA SETS-----" | Out-File -FilePath $logFile -Append

    $fileData = Get-DirectoryAndFilename -filepaths $downloadedItems
        
        foreach ($file in $fileData)
        {
            $downloadedFilePath = "$($file.Directory)\$($file.Filename)"
            $phpInsert = ""

            switch($file.DataType) {

                "DCHVL" { $phpInsert = "mds_dchvl"; break }
                "DISCH" { $phpInsert = "mds_disch"; break }
                "DPIDS" { $phpInsert = "mds_dpids"; break }
                "ERRTRAN" { $phpInsert = "mds_errtran"; break }
                "LGUNR" { $phpInsert = "mds_lgunr"; break }
                "MDPID" { $phpInsert = "mds_mdpid"; break }
                "METER" { $phpInsert = "mds_meter"; break }
                "MNETS" { $phpInsert = "mds_mnets"; break }
                "MRA" { $phpInsert = "mds_mra"; break }
                "MSPID" { $phpInsert = "mds_mspid"; break }
                "NAPS" { $phpInsert = "mds_naps"; break }
                "READS" { $phpInsert = "mds_reads"; break }
                "SSPID" { $phpInsert = "mds_sspids"; break }
                "SSSCO" { $phpInsert = "mds_sssco"; break }
                "TEADJ" { $phpInsert = "mds_teadj"; break }
                "TRCNCL" { $phpInsert = "mds_trcncl"; break }
                "TRIF" { $phpInsert = "mds_tariff"; break }
                "UNPAIR" { $phpInsert = "mds_unpair"; break }
                "VLADJ" { $phpInsert = "mds_vladj"; break }
                "WSPID" { $phpInsert = "mds_wspid"; break }
                "WSSCO" { $phpInsert = "mds_wssco"; break }

            }

            # If file is a market message, it requires extracting the .gz contents, reading the XML data and inserting it into the designated table
            if($file.DataType -eq "msg")
            {

                "[$((Get-Date).ToString())] Writing data from the market messages file $($file.Filename) to the mds_marketmessages table." | Out-File -FilePath $logFile -Append

                Start-Process python -ArgumentList "$marketMessageWriteScriptPath", "$downloadedFilePath" -Wait -RedirectStandardOutput $marketMessageWriteScriptOutputLogPath -RedirectStandardError $marketMessageWriteScriptErrorLogPath

                "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
            }
            else 
            {                    
                "[$((Get-Date).ToString())] Writing data from the $($file.Filename) file to the $($phpInsert) table." | Out-File -FilePath $logFile -Append

                Start-Process php -ArgumentList "../insert/$phpInsert.php", "$downloadedFilePath" -Wait

                "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
            }
        }

    "[$((Get-Date).ToString())] -----SCRIPT EXECUTION COMPLETED-----" | Out-File -FilePath $logFile -Append
}
else 
{
    "[$((Get-Date).ToString())] No files were downloaded" | Out-File -FilePath $logFile -Append
    "[$((Get-Date).ToString())] -----SCRIPT EXECUTION COMPLETED-----" | Out-File -FilePath $logFile -Append
}