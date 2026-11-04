# load_infile

Data ingestion pipeline for the **SMARTA energy market data system (MDS)**.

It pulls files from an FTPS server, then routes them by type into MySQL:

- CSV market data files → bulk-loaded via PHP (`LOAD DATA LOCAL INFILE`)
- Gzipped SOAP/XML market messages → parsed and loaded via Python
- Zipped settlement archives → extracted and loaded via Python

## Architecture

```
FTP Server → scripts/ftp.ps1 (PowerShell, WinSCP)
                ├── *.gz files   → open_gz_messages.py → read_xml_messages.py → write_xml_messages.py → MySQL
                ├── *.zip files  → open_zip_settlements.py → read_settlements.py → write_dataframe.py → MySQL
                └── *.csv files  → insert/mds_*.php (LOAD DATA LOCAL INFILE) → MySQL
```

`scripts/ftp.ps1` is the orchestrator: it syncs the remote FTP folder locally, categorises
downloaded files by data type, dispatches each to the right handler, and logs the run.

### Data types → handlers

| Data type(s) | Handler |
|---|---|
| Settlements: `P1`, `R1`, `R2`, `R3`, `RF` | `scripts/python/open_zip_settlements.py` |
| Market messages: `msg` | `scripts/python/open_gz_messages.py` |
| Everything else: `DCHVL`, `DISCH`, `DPIDS`, `ERRTRAN`, `LGUNR`, `MDPID`, `METER`, `MNETS`, `MRA`, `MSPID`, `NAPS`, `READS`, `SSPID`, `SSSCO`, `TEADJ`, `TRCNCL`, `TRIF`, `UNPAIR`, `VLADJ`, `WSPID`, `WSSCO` | `insert/mds_<type>.php` |

Two naming exceptions: `TRIF` → table `mds_tariff`, `SSPID` → table `mds_sspids`.

### Databases

Two MySQL targets, both using database `mds-current` (credentials are hardcoded per-file,
not pulled from `.env`):

- **AWS RDS** (`singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com:3306`) — used by the PHP insert scripts and `write_dataframe.py` (settlements)
- **DigitalOcean** (`db-mysql-mds-live-smarta-do-user-*.b.db.ondigitalocean.com:25060`) — used by `write_data.py` and `write_xml_messages.py`

## Repository layout

```
scripts/
  ftp.ps1                     Main orchestrator (PowerShell + WinSCP)
  python/
    open_gz_messages.py        Decompress .gz, extract SOAP/XML messages
    read_xml_messages.py       Parse the extracted XML
    write_xml_messages.py      Load parsed messages into MySQL (DigitalOcean)
    open_zip_settlements.py    Unzip settlement archives
    read_settlements.py        Read/normalise extracted settlement CSVs, concatenate
    write_dataframe.py         Load a DataFrame into MySQL (AWS RDS) via SQLAlchemy
    write_data.py              Load a single CSV into MySQL (DigitalOcean) via pandas
    write_data_individual.py   Variant of write_data.py for individual-file loads
    compare_csvs.py            Diff a new CSV against the previous upload
    compare_csvs_individual.py Same, with explicit new/previous file paths as CLI args
    convert_column_names.py    Normalise column names to camelCase
insert/
  mds_*.php                    One loader per MDS data type (LOAD DATA LOCAL INFILE)
  cma_*.php                    Same pattern, for CMA-prefixed tables
  insertSettlementP1a.php,     Legacy PHP-based settlement loaders
  insertSettlementP1d1.php     (predate the Python ZIP pipeline)
includes/
  database.php                 PDO connection (LOCAL_INFILE enabled) — AWS RDS
  databaseDev.php               Dev/alternate DB connection
  functions.php                 mdsInsert(): truncates table, then bulk-loads a CSV
config/
  .env                          Runtime config for ftp.ps1 (not checked in)
```

## How it works

### PHP insert scripts (`insert/`)

Every `mds_*.php` / `cma_*.php` script follows the same pattern:

1. Include `../includes/database.php` and `../includes/functions.php`
2. Read the file path from `$argv[1]`
3. Define `$tableName` and `$dataset` (the ordered list of columns)
4. Call `mdsInsert($pdo, $filePath, $tableName, $dataset, $separator, $lines)`

`mdsInsert()` runs `TRUNCATE TABLE` before every load, so **each run fully replaces the
table's contents** — this is not an incremental append.

### Python settlement pipeline

`open_zip_settlements.py` unzips an archive to `Settlement\unzipped\`, then
`read_settlements.py` reads each extracted CSV, converts its column names to camelCase,
tags rows with a `sourceFileName` column, concatenates everything, and hands the combined
DataFrame to `write_dataframe.py`, which appends it to `mds_<table>` on AWS RDS.

### Python market-message pipeline

`open_gz_messages.py` decompresses a `.gz` file and extracts the SOAP/XML messages inside,
`read_xml_messages.py` parses them, and `write_xml_messages.py` appends the result to
`mds_<table>` on the DigitalOcean database.

### CSV diffing

`compare_csvs.py` / `compare_csvs_individual.py` diff a newly downloaded CSV against the
previous upload, write any new rows to `differences/unique_<file>.csv`, and call
`write_data.py` to load just the new rows if any are found.

## Running

**Full pipeline** (Windows, requires WinSCP):

```powershell
powershell -File scripts/ftp.ps1
```

Configuration (FTP host/credentials, local/remote paths, helper script locations, log
path, data type list) is read from `config/.env` via the `Import-DotEnv` function at the
top of `ftp.ps1`.

**PHP insert scripts standalone**:

```bash
php insert/mds_reads.php "/path/to/file.csv"
```

**Python scripts standalone**:

```bash
python3 scripts/python/open_gz_messages.py "/path/to/file.gz"
python3 scripts/python/open_zip_settlements.py "/path/to/file.zip"
python3 scripts/python/compare_csvs_individual.py new.csv prev.csv , 1 log.txt
python3 scripts/python/write_data.py "/path/to/file.csv" tablename
```

## Requirements

- **PowerShell** with the WinSCP .NET assembly (orchestration runs on Windows)
- **PHP** with PDO MySQL, `local_infile` enabled on both the client and the MySQL server
- **Python 3** with `pandas` and `sqlalchemy`

## Notes

- Database credentials are hardcoded in `includes/database.php`, `includes/databaseDev.php`,
  and the Python `write_*.py` scripts rather than loaded from `config/.env` — treat those
  files as sensitive and keep them out of any public fork.
- `mdsInsert()` truncates its target table on every run; there's no append/upsert mode for
  the PHP-driven CSV types.
