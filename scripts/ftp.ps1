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

# Specify the delimiter and the header row in the csv files used for comparison
$compCsvDelimiter = ','
$compCsvheader = 1

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
    "READS",
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

    $fileData = Get-DirectoryAndFilename -filepaths $downloadedItemsToCompare # <---------------------------------- deleted var
        
        foreach ($file in $fileData)
        {
            $downloadedFilePath = "$($file.Directory)\$($file.Filename)"

            # If file is a market message, it requires extracting the .gz contents, reading the XML data and inserting it into the designated table
            if($file.DataType -eq "msg")
            {

                "[$((Get-Date).ToString())] Writing data from the market messages file $($file.Filename) to the marketmessages table." | Out-File -FilePath $logFile -Append

                Start-Process python -ArgumentList "$marketMessageWriteScriptPath", "$downloadedFilePath" -Wait -RedirectStandardOutput $marketMessageWriteScriptOutputLogPath -RedirectStandardError $marketMessageWriteScriptErrorLogPath

                "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
            }
            else 
            {
                # Get the filepath of where the diff file would be stored if it already exists
                $diffPath = $file.Directory + "\differences\unique_" + $file.Filename

                # Check if the diff file already exists - if it does, skip the current iteration of the loop
                if (Test-Path $diffPath -PathType Leaf)
                {
                    "[$((Get-Date).ToString())] Diff file already exists for the downloaded file - skipping: $diffPath" | Out-File -FilePath $logFile -Append
                    "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append

                    continue
                }

                    # Handle the edge cases - data types that does no follow either a common naming convention (5 char string) and/or common table name convention (named as mds_*data type*)
                    if ($file.DataType -eq "TRIF")
                    {
                        "[$((Get-Date).ToString())] No preceeding file was found. Writing entire contents of $($file.Filename) to the tariff table." | Out-File -FilePath $logFile -Append

                        Start-Process python -ArgumentList "$writeScriptPath", "$downloadedFilePath", "tariff" -Wait -RedirectStandardOutput $writeScriptOutputLogPath -RedirectStandardError $writeScriptErrorLogPath

                        "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
                        
                        continue
                    }

                    if ($file.DataType -eq "SSPID")
                    {
                        "[$((Get-Date).ToString())] No preceeding file was found. Writing entire contents of $($file.Filename) to the sspids table." | Out-File -FilePath $logFile -Append

                        Start-Process python -ArgumentList "$writeScriptPath", "$downloadedFilePath", "sspids" -Wait -RedirectStandardOutput $writeScriptOutputLogPath -RedirectStandardError $writeScriptErrorLogPath

                        "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append
                        
                        continue
                    }
                    
                    "[$((Get-Date).ToString())] No preceeding file was found. Writing entire contents of $($file.Filename) to the $($file.DataType) table." | Out-File -FilePath $logFile -Append

                    Start-Process python -ArgumentList "$writeScriptPath", "$downloadedFilePath", "$($file.DataType)" -Wait -RedirectStandardOutput $writeScriptOutputLogPath -RedirectStandardError $writeScriptErrorLogPath

                    "[$((Get-Date).ToString())] ----------" | Out-File -FilePath $logFile -Append

                # Append to the log based on what was returned by the python script
                if ($LASTEXITCODE -ne 0)
                {
                    "[$((Get-Date).ToString())] Unique rows have been saved to $($file.Directory)\differences\unique_$($file.Filename)" | Out-File -FilePath $logFile -Append
                }
                else 
                {
                    "[$((Get-Date).ToString())] No differences were found between the files" | Out-File -FilePath $logFile -Append
                }

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