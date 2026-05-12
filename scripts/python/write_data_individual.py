import pandas as pd
from sqlalchemy import create_engine
import os

filepath = r'C:\Users\Jakub\Desktop\csv\MDS_READS_SMARTA_R_E20240425_G2024042601.csv'
tabname = 'reads'

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

    df.columns = convertColumnNames(df.columns)

    # If no data was found
    if len(df.columns) < 1:
        raise ValueError("CSV file must contain at least one column")

    # MySQL database connection information
    username = 'admin'
    password = 'WkZe8euII9BhqGT8kSxW4mde6D8A8GAc'
    host = 'singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com'
    database_name = 'mds-current'
    port = '3306'

    # Create a connection string for MySQL
    connection_str = f"mysql+pymysql://{username}:{password}@{host}:{port}/{database_name}"

    # Create a connection to the MySQL database
    engine = create_engine(connection_str)

    # Write the DataFrame to the MySQL database table 
    df.to_sql(f"mds_{table_name}", con=engine, if_exists='append', index=False)

def convertColumnNames(column_names):
    converted_names = []
    for name in column_names:
        name_modified = ''.join(char for char in name if char.isalnum())  # Remove non-alphanumeric characters and join the result together
        converted = name_modified[0].lower() + name_modified[1:]
        converted_names.append(converted)
    return converted_names

if data_source is not None and table_name is not None:
    write_data(data_source, table_name)
