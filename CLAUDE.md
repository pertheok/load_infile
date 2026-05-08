# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Project Does

This is a **data ingestion pipeline** for the SMARTA energy market data system (MDS). It:
1. Downloads CSV and compressed XML files from an FTPS server via PowerShell (WinSCP)
2. Inserts CSV data into MySQL using `LOAD DATA LOCAL INFILE` (via PHP) or pandas (via Python)
3. Parses SOAP/XML market messages from gzipped files and inserts them into MySQL

## Running Scripts

**PowerShell orchestration** (run on Windows, uses WinSCP):
```powershell
powershell -File scripts/ftp.ps1
```

**PHP insert scripts** (called by ftp.ps1, but can be run standalone):
```bash
php insert/mds_reads.php "/path/to/file.csv"
```

**Python scripts** (called by ftp.ps1 or standalone):
```bash
python3 scripts/python/compare_csvs.py "/path/to/file.csv"
python3 scripts/python/open_gz_messages.py "/path/to/file.gz"
python3 scripts/python/write_data.py "/path/to/file.csv" tablename
```

Configuration lives in `config/.env` and is loaded by `ftp.ps1` at runtime.

## Architecture

### Core Flow

```
FTP Server → ftp.ps1 (PowerShell)
                ├── *.csv files → PHP insert/mds_*.php → MySQL (LOAD DATA LOCAL INFILE)
                └── *.gz files  → Python open_gz_messages.py → MySQL (pandas/SQLAlchemy)
```

### Key Files

| File | Purpose |
|------|---------|
| `scripts/ftp.ps1` | Main orchestrator: syncs FTP, categorises files, routes to handlers, logs everything |
| `includes/functions.php` | `mdsInsert($pdo, $filePath, $tableName, $dataset, $separator, $lines)` — truncates table then bulk-loads CSV |
| `includes/database.php` | PDO connection with `LOCAL_INFILE` enabled; credentials come from hardcoded values (override via `config/.env`) |
| `includes/pageAudit.php` | Records script name, action, and execution time to `site_audit` table |
| `scripts/python/compare_csvs.py` | Diffs new CSV against most-recent upload; writes `differences/unique_<file>.csv`; calls `write_data.py` if new rows found |
| `scripts/python/write_data.py` | Loads CSV with pandas, converts columns to camelCase, appends to `mds_<table>` |
| `scripts/python/open_gz_messages.py` | Decompresses .gz, extracts individual XML SOAP messages, calls `read_xml_messages.py`, inserts via `write_xml_messages.py` |

### PHP Insert Scripts (`insert/`)

All 28 scripts follow an identical pattern:
1. Include `../includes/database.php` and `../includes/functions.php`
2. Read file path from `$argv[1]`
3. Define `$tableName` and `$dataset` (ordered column list)
4. Call `mdsInsert()` then `pageAudit()`

The `mdsInsert()` function issues `TRUNCATE TABLE` before every load — each run replaces the full table contents.

### Data Types

`DATA_TYPES` in `config/.env` lists the known file-type keywords (DCHVL, DISCH, READS, msg, TRIF, etc.). `ftp.ps1` maps each keyword to the correct handler script. `msg` files go to Python; all others go to PHP.

### Databases

- **Production**: AWS RDS (`singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com`) — used by PHP scripts
- **Live (Python)**: DigitalOcean managed MySQL (`db-mysql-mds-live-smarta-do-user-*.b.db.ondigitalocean.com:25060`) — used by Python scripts
- Both target database `mds-current`

Credentials are currently hardcoded in `includes/database.php` and the Python scripts. The `config/.env` file holds FTP credentials and script paths but is not directly read by PHP.
