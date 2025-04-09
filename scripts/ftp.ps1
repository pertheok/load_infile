# Load WinSCP .NET assembly
Add-Type -Path "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"

# ----- OPTIONS -----

# Define the directories - remote will be mirrored locally
$remotePath = '/SMARTA_R/'
$localPath = '\\EC2AMAZ-BKV9RT4\AWS-S3-MDS\SMARTA_R\'

# Locations of the helper scripts used here

# Market messages
$marketMessageWriteScriptPath = 'C:\laragon\www\CMOS-connect\scripts\python\open_gz_messages.py'
$marketMessageWriteScriptOutputLogPath = "C:\laragon\www\CMOS-connect\scripts\logs\python_output_market_message.txt"
$marketMessageWriteScriptErrorLogPath = "C:\laragon\www\CMOS-connect\scripts\logs\python_error_market_message.txt"

# Settlements
$settlementsWriteScriptPath = 'C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\python\open_zip_settlements.py'
$settlementsWriteScriptOutputLogPath = "C:\laragon\www\CMOS-connect\scripts\logs\python_output_settlements.txt"
$settlementsWriteScriptErrorLogPath = "C:\laragon\www\CMOS-connect\scripts\logs\python_error_settlements.txt"

# PHP inserts directory
$phpScriptsDir = 'C:\laragon\www\CMOS-connect\scripts\load_infile\insert';

# PHP executable location
$phpExecLocation = "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"

# Session log is stored here
$logFile = "C:\laragon\www\CMOS-connect\scripts\logs\log.txt"

# Array containing the substrings to search for in the $downloadedItems array
$dataTypes = @(
    "DCHVL",    
    "DISCH",
    "DPIDS",
    "ERRTRAN",
    "LGUNR",
    "MDPID",
    "METER",
    "MNETS",
    "MRA",
    "msg",
    "MSPID",
    "NAPS",
    "P1",
    "R1",
    "R2",
    "R3",
    "READS",
    "RF",
    "SSPID",
    "SSSCO",
    "TEADJ",
    "TRCNCL",
    "TRIF",
    "UNPAIR",
    "WSPID",
    "WSSCO",
    "VLADJ"    
)

# FTP session related variables
$hostName = "automated.cmosservice.co.uk"
$userName = "anonymous"
$certPath = "C:\laragon\www\CMOS-connect\certificates\FTP_Smarta_RT_Portal_1_2025.pfx"
$certPass = "UWWPOLNGCQGS"

# Set up session options
$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol = [WinSCP.Protocol]::Ftp
    HostName = $hostName
    UserName = $userName
    FtpSecure = [WinSCP.FtpSecure]::Explicit
    TlsClientCertificatePath = $certPath
    PrivateKeyPassphrase = $certPass
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
                "P1" { $phpInsert = "mds_settlements"; break }
                "R1" { $phpInsert = "mds_settlements"; break }
                "R2" { $phpInsert = "mds_settlements"; break }
                "R3" { $phpInsert = "mds_settlements"; break }
                "READS" { $phpInsert = "mds_reads"; break }
                "RF" { $phpInsert = "mds_settlements"; break }
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
            elseif ($file.DataType -eq "P1" -or $file.DataType -eq "R1" -or $file.DataType -eq "R2" -or $file.DataType -eq "R3" -or $file.DataType -eq "RF") {
                "[$((Get-Date).ToString())] Writing data from the settlements file $($file.Filename) to the mds_settlements table." | Out-File -FilePath $logFile -Append

                Start-Process python -ArgumentList "$settlementsWriteScriptPath", "$downloadedFilePath" -Wait -RedirectStandardOutput $settlementsWriteScriptOutputLogPath -RedirectStandardError $settlementsWriteScriptErrorLogPath

                "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
            }
            else 
            {                    
                "[$((Get-Date).ToString())] Writing data from the $($file.Filename) file to the $($phpInsert) table." | Out-File -FilePath $logFile -Append

                $phpOutputLog = "C:\laragon\www\CMOS-connect\scripts\logs\php_output_$($phpInsert).txt"
                $phpErrorLog = "C:\laragon\www\CMOS-connect\scripts\logs\php_error_$($phpInsert).txt"

                Start-Process $phpExecLocation `
                    -ArgumentList "$($phpScriptsDir)\$($phpInsert).php", "$downloadedFilePath" `
                    -WorkingDirectory $phpScriptsDir `
                    -Wait `
                    -RedirectStandardOutput $phpOutputLog `
                    -RedirectStandardError $phpErrorLog

                # Log that the PHP script was run and where logs are stored
                "[$((Get-Date).ToString())] Ran PHP script for $($phpInsert). Output: $phpOutputLog | Error: $phpErrorLog" | Out-File -FilePath $logFile -Append


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