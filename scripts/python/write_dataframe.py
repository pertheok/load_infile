from sqlalchemy import create_engine

def write_dataframe(dataframe, table_name):

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
    dataframe.to_sql(f"mds_{table_name}", con=engine, if_exists='append', index=False)