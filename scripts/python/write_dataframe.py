from sqlalchemy import create_engine
from load_env import db_connection_str

def write_dataframe(dataframe, table_name):

    # MySQL connection information is read from config/.env

    # To target the DigitalOcean database instead, use db_connection_str('DO_DB')


    # Create a connection string for MySQL
    connection_str = db_connection_str()

    # Create a connection to the MySQL database
    engine = create_engine(connection_str)

    # Write the DataFrame to the MySQL database table 
    dataframe.to_sql(f"mds_{table_name}", con=engine, if_exists='append', index=False)