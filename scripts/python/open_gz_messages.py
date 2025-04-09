import gzip
import sys
import os
import re
import pandas as pd
from read_xml_messages import read_xml_messages
from write_dataframe import write_dataframe
from convert_column_names import convert_column_names

filepath = sys.argv[1]

def open_gz_messages(messages_file_path):

    # Name of the currently processed file, will be appended to the dataframe
    message_filename = os.path.basename(messages_file_path)

    # Array that will be populated with messages and converted to a dataframe later on
    read_messages = []

    # Open and read the .gz file contents
    with gzip.open(messages_file_path, 'rb') as f:
        data = f.read()
        data = data.decode('utf-8')

    # Delimtiers used to separate individual messages
    start_delimiter = r'<?xml version="1.0" encoding="UTF-8"?>'
    end_delimiter = r'</SOAP-ENV:Envelope>'

    # Use a regular expression to find all occurrences of the content between start and end delimiters, inclusive
    xmls = re.findall(r'({}.*?{})'.format(re.escape(start_delimiter), re.escape(end_delimiter)), data, re.DOTALL)

    # Iterate through the xmls
    for xml_content in xmls:
        
        # Declare the substring used to identify business messages which are to be ignored
        substring = 'ns0:BusinessMessage'

        # If the xml being looked at is a business message, ignore it - otherwise, read its data
        if substring in xml_content:
            continue
        else:
            xml_data = read_xml_messages(xml_content)
            read_messages.extend(xml_data)

    # Convert the messages to a dataframe
    df = pd.DataFrame(read_messages)

    # Append a column with the filename to the dataframe
    df['sourceFileName'] = message_filename

    # Convert the column names in the dataframe so they match the naming convention in the table
    df.columns = convert_column_names(df.columns)

    # Write the dataframe into the database
    write_dataframe(df, 'marketmessages')

open_gz_messages(filepath)
