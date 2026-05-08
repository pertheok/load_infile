# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Project Does

This is a **data ingestion pipeline** for the SMARTA energy market data system (MDS). It:
1. Downloads CSV and compressed files from an FTPS server via PowerShell (WinSCP)
2. Inserts CSV data into MySQL using `LOAD DATA LOCAL INFILE` (via PHP)
3. Parses SOAP/XML market messages from gzipped files and inserts them into MySQL
4. Extracts and loads settlement data from ZIP archives into MySQL

## Running Scripts

**PowerShell orchestration** (runs on Windows, uses WinSCP):
```powershell
powershell -File scripts/ftp.ps1
```

**PHP insert scripts** (called by ftp.ps1, but can be run standalone):
```bash
php insert/mds_reads.php "/path/to/file.csv"
```

**Python scripts** (called by ftp.ps1 or standalone):
```bash
python3 scripts/python/open_gz_messages.py "/path/to/file.gz"
python3 scripts/python/open_zip_settlements.py "/path/to/file.zip"
python3 scripts/python/compare_csvs_individual.py new.csv prev.csv , 1 log.txt
python3 scripts/python/write_data.py "/path/to/file.csv" tablename
```

Configuration lives in `config/.env` and is loaded by `ftp.ps1` at runtime via `Import-DotEnv`.

## Architecture

### Core Flow

```
FTP Server → ftp.ps1 (PowerShell)
                ├── *.gz files   → open_gz_messages.py → read_xml_messages.py → write_xml_messages.py → MySQL
                ├── *.zip files  → open_zip_settlements.py → read_settlements.py → write_dataframe.py → MySQL
                └── *.csv files  → PHP insert/mds_*.php (LOAD DATA LOCAL INFILE) → MySQL
```

### Key Files

| File | Purpose |
|------|---------|
| `scripts/ftp.ps1` | Main orchestrator: syncs FTP, categorises files by data type, routes to handlers, logs everything |
| `includes/functions.php` | `mdsInsert($pdo, $filePath, $tableName, $dataset, $separator, $lines)` — truncates table then bulk-loads CSV |
| `includes/database.php` | PDO connection with `LOCAL_INFILE` enabled |
| `includes/pageAudit.php` | Records script name, action, and execution time to `site_audit` table |
| `scripts/python/open_gz_messages.py` | Decompresses .gz, extracts XML SOAP messages, delegates to `read_xml_messages.py` + `write_xml_messages.py` |
| `scripts/python/open_zip_settlements.py` | Unzips settlement archive to `\Settlement\unzipped\`, delegates list of CSVs to `read_settlements.py` |
| `scripts/python/read_settlements.py` | Reads each extracted settlement CSV, converts column names to camelCase, adds `sourceFileName`, concatenates and calls `write_dataframe.py` |
| `scripts/python/write_dataframe.py` | Writes a DataFrame to `mds_<table>` on AWS RDS via SQLAlchemy `to_sql(if_exists='append')` |
| `scripts/python/write_data.py` | Loads a CSV with pandas, converts column names, appends to `mds_<table>` on DigitalOcean MySQL |
| `scripts/python/compare_csvs.py` | Diffs new CSV against previous upload; writes `differences/unique_<file>.csv`; calls `write_data.py` if new rows found |
| `scripts/python/compare_csvs_individual.py` | Same as above but takes explicit new/previous file paths as CLI args (5 args: new, prev, delimiter, header, log_path) |
| `scripts/python/convert_column_names.py` | Strips non-alphanumeric chars, returns camelCase column names |

### PHP Insert Scripts (`insert/`)

All `mds_*.php` scripts follow an identical pattern:
1. Include `../includes/database.php` and `../includes/functions.php`
2. Read file path from `$argv[1]`
3. Define `$tableName` and `$dataset` (ordered column list)
4. Call `mdsInsert()` then `pageAudit()`

`mdsInsert()` issues `TRUNCATE TABLE` before every load — each run replaces the full table contents.

`insert/insertSettlementP1a.php` and `insertSettlementP1d1.php` are legacy PHP-based settlement loaders (predating the Python ZIP pipeline).

`insert/cma_*.php` scripts follow the same pattern but target CMA-prefixed tables.

### Data Types → Handlers

Settlement types (P1, R1, R2, R3, RF) → `open_zip_settlements.py` (Python)  
Market messages (msg) → `open_gz_messages.py` (Python)  
All other types (DCHVL, DISCH, DPIDS, ERRTRAN, LGUNR, MDPID, METER, MNETS, MRA, MSPID, NAPS, READS, SSPID, SSSCO, TEADJ, TRCNCL, TRIF, UNPAIR, VLADJ, WSPID, WSSCO) → `mds_<type>.php` (PHP)

`TRIF` maps to table `mds_tariff`; `SSPID` maps to `mds_sspids` (pluralised).

### Databases

- **AWS RDS** (`singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com:3306`) — used by PHP scripts and `write_dataframe.py` (settlements)
- **DigitalOcean** (`db-mysql-mds-live-smarta-do-user-*.b.db.ondigitalocean.com:25060`) — used by `write_data.py` and `write_xml_messages.py`
- Both target database `mds-current`; credentials are hardcoded in each file
