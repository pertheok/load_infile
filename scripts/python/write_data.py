import pandas as pd
from sqlalchemy import create_engine
import os
import sys
from convert_column_names import convert_column_names

data_source = None
table_name = None
if len(sys.argv[2]) <= 7:
    data_source = sys.argv[1]
    table_name = sys.argv[2].lower()

def write_data(csv_data, table_name):

    downloaded_filename = os.path.basename(csv_data)

    # Check if a filename suggests that the file contains only diffs
    if "unique_" in downloaded_filename:

        # Read CSV file into a Pandas DataFrame
        df = pd.read_csv(csv_data)
    
    else:
        # Read CSV file into a Pandas DataFrame, but specify that in a raw data file column headers are in row index 1
        df = pd.read_csv(csv_data, delimiter=',', header=1, dtype='object')

        # Add a column that specifies the source file for the data:
        df['sourceFileName'] = downloaded_filename

    df.columns = convert_column_names(df.columns)

    # If no data was found
    if len(df.columns) < 1:
        raise ValueError("CSV file must contain at least one column")

    # MySQL database connection information
    username = 'admin'
    password = 'WkZe8euII9BhqGT8kSxW4mde6D8A8GAc'
    host = 'singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com'
    database_name = 'mds-current'
    port = '25060'

    # Create a connection string for MySQL
    connection_str = f"mysql+pymysql://{username}:{password}@{host}:{port}/{database_name}"

    # Create a connection to the MySQL database
    engine = create_engine(connection_str)

    # Write the DataFrame to the MySQL database table
    df.to_sql(f"mds_{table_name}", con=engine, if_exists='append', index=False)

if data_source is not None and table_name is not None:
    write_data(data_source, table_name)