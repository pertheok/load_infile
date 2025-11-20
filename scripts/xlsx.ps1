# ============================================================
# CONFIGURATION
# ============================================================

$marketMessageXlsxWriteScriptPath = 'C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\python\open_xlsx_messages.py'
$marketMessageWriteScriptOutputLogPath = "C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\logs\python_output_market_message.txt"
$marketMessageWriteScriptErrorLogPath = "C:\laragon\www\CMOS-connect\scripts\load_infile\scripts\logs\python_error_market_message.txt"
$logFile = "C:\laragon\www\CMOS-connect\scripts\logs\log.txt"

$watchPath = "B:\SMARTA-XLSX\"
$filter = "*.xlsx"
$checkIntervalSeconds = 60

# ============================================================
# INITIALIZATION
# ============================================================

$stateFile = "$env:TEMP\last_file_check_timestamp.txt"

# If no state file exists, create one with an old timestamp
if (!(Test-Path $stateFile)) {
    (Get-Date "2000-01-01") | Out-File $stateFile -Force
}

# Read only the FIRST line and trim whitespace
$lastCheckString = (Get-Content $stateFile -ErrorAction SilentlyContinue | Select-Object -First 1).Trim()

# Parse timestamp safely
try {
    $lastCheck = Get-Date $lastCheckString
}
catch {
    # If the file is corrupted or unreadable, reset timestamp
    $lastCheck = Get-Date "2000-01-01"
    (Get-Date "2000-01-01") | Out-File $stateFile -Force
}

"[$((Get-Date).ToString())] Script started, last check: $lastCheck" | Out-File -FilePath $logFile -Append
Write-Host "Monitoring $watchPath for new files every $checkIntervalSeconds seconds..."

# ============================================================
# MAIN LOOP
# ============================================================

while ($true) {

    # Get new files based on LastWriteTime > last check
    $newFiles = Get-ChildItem -Path $watchPath -Filter $filter -File -ErrorAction SilentlyContinue |
                Where-Object { $_.LastWriteTime -gt $lastCheck }

    foreach ($file in $newFiles) {

        # Log detection
        "[$((Get-Date).ToString())] New file detected: $($file.FullName)" | Out-File -FilePath $logFile -Append

        # Run Python script on the file
        Start-Process python `
            -ArgumentList "$marketMessageXlsxWriteScriptPath", "`"$($file.FullName)`"" `
            -Wait `
            -RedirectStandardOutput $marketMessageWriteScriptOutputLogPath `
            -RedirectStandardError $marketMessageWriteScriptErrorLogPath

        # Log Python execution
        "[$((Get-Date).ToString())] Python script executed for file: $($file.FullName)" | Out-File -FilePath $logFile -Append
    }

    # Update last check timestamp
    (Get-Date) | Out-File $stateFile -Force
    $lastCheck = Get-Date

    # Wait before next scan
    Start-Sleep -Seconds $checkIntervalSeconds
}