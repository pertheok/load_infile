$marketMessageXlsxWriteScriptPath = 'C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\python\open_xlsx_messages.py'
$marketMessageWriteScriptOutputLogPath = "C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\logs\python_output_market_message.txt"
$marketMessageWriteScriptErrorLogPath = "C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\logs\python_error_market_message.txt"

$logFile = "C:\laragon\www\CMOS-connect\scripts\logs\log.txt"

$watch = New-Object System.IO.FileSystemWatcher
$watch.Path = "C:\SMARTA-XLSX\"
$watch.Filter = "*.xlsx"
$watch.EnableRaisingEvents = $true

$action = {
    $file = $Event.SourceEventArgs.FullPath

    Start-Process python `
        -ArgumentList "$marketMessageXlsxWriteScriptPath", "`"$file`"" `
        -Wait `
        -RedirectStandardOutput $marketMessageWriteScriptOutputLogPath `
        -RedirectStandardError $marketMessageWriteScriptErrorLogPath

    "[$((Get-Date).ToString())] triggered on file: $file" | Out-File -FilePath $logFile -Append
}

Register-ObjectEvent $watch Created -Action $action

while ($true) { Start-Sleep 5 }