import pandas as pd
from sqlalchemy import create_engine
import os

filepath = r'\\EC2AMAZ-BKV9RT4\DO_Smarta\watercrm-storage-smarta\SMARTA_R\Business_Transaction_Dossiers\TRCNCL_SMARTA_R_E202403_G2024041108.csv'
tabname = 'trcncl'

def write_data(csv_data, table_name):

    # Read CSV file into a Pandas DataFrame
    df = pd.read_csv(csv_data, delimiter=',', header=1, dtype='object')

    # Check if 'sourceFileName' column exists
    #if 'sourceFileName' not in df.columns:
        # Below is to be used only if you want to load in the whole dataset instead of just diffs
    downloaded_filename = os.path.basename(csv_data)

        # Add a column that specifies the source file for the data:
    df['sourceFileName'] = downloaded_filename

    df.columns = convertColumnNames(df.columns)

    # If no data was found
    if len(df.columns) < 1:
        raise ValueError("CSV file must contain at least one column")

    # MySQL database connection information
    username = 'doadmin'
    password = 'AVNS_FzyD2YkqUcoZUepHZbj'
    host = 'db-mysql-mds-live-smarta-do-user-14108508-0.b.db.ondigitalocean.com'
    database_name = 'mds-test'
    port = '25060'
    # username = 'root'
    # password = ''
    # host = '127.0.0.1'
    # database_name = 'mds-current'

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

write_data(filepath, tabname)
