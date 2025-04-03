from sqlalchemy import create_engine

def write_xml_messages(dataframe):

    # MySQL database connection information
    username = 'doadmin'
    password = 'AVNS_FzyD2YkqUcoZUepHZbj'
    host = 'db-mysql-mds-live-smarta-do-user-14108508-0.b.db.ondigitalocean.com'
    database_name = 'mds-current'
    port = '25060'
    table_name = 'marketmessages'

    # Create a connection string for MySQL
    connection_str = f"mysql+pymysql://{username}:{password}@{host}:{port}/{database_name}"

    # Create a connection to the MySQL database
    engine = create_engine(connection_str)

    # Write the DataFrame to the MySQL database table 
    dataframe.to_sql(f"mds_{table_name}", con=engine, if_exists='append', index=False)