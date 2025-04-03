import pandas as pd
import os
import sys
from datetime import datetime
import warnings
from write_data import write_data

# Suppress warnings as they cause unexpected behaviour and their content is irrelevant in case of the below code
warnings.filterwarnings("ignore")

# Capture arguments provided by the powershell script

# new_file = sys.argv[1]
# previous_file = sys.argv[2]
# csv_delimiter = sys.argv[3]
# csv_header = int(sys.argv[4]) # Requires a conversion to an int as it's received as a string
# comp_log_path = sys.argv[5]

# For testing on local

# new_file = r"C:\Users\Jakub\Desktop\csv\MDS_METER_SMARTA_R_E20240416_G2024041701.csv"
# previous_file = r"C:\Users\Jakub\Desktop\csv\MDS_METER_SMARTA_R_E20240415_G2024041601.csv"
# csv_delimiter = ','
# csv_header = 1
# comp_log_path = r'C:\Users\Jakub\Desktop\csv\comparison_log.txt'

# For testing on RDP

new_file = r"\\EC2AMAZ-BKV9RT4\DO_Smarta\watercrm-storage-smarta\SMARTA_R\Connection_Register\MDS_METER_SMARTA_R_E20240417_G2024041801.csv"
previous_file = r"\\EC2AMAZ-BKV9RT4\DO_Smarta\watercrm-storage-smarta\SMARTA_R\Connection_Register\MDS_METER_SMARTA_R_E20240416_G2024041701.csv"
csv_delimiter = ','
csv_header = 1
comp_log_path = r'C:\laragon\www\CMOS-connect\scripts\logs\comparison_log.txt'

def compare_csvs(downloaded_file_path, previous_most_recent_upload_path, delimiter, header, comparison_log_path):

    # Check the filename for a keyword to determine which associated table the data is supposed to go in (keyword: table name)
    tables = {
        '?': 'archive',
        'DCHVL': 'dchvl',
        'DISCH': 'disch',
        'DPIDS': 'dpids',
        'MDPID': 'mdpid',
        'METER': 'meter',
        'MNETS': 'mnets',
        'MSPID': 'mspid',
        'READS': 'reads',
        'SSPID': 'sspids', # Table name is not a typo
        'SSSCO': 'sssco',
        'TEADJ': 'teadj',
        'TRIF': 'tariff', # Keyword is not a typo
        'WSPID': 'wspid',
        'WSSCO': 'wssco',
        'VLADJ': 'vladj'
    }

    table_name = None

    for key in tables:
        if key in downloaded_file_path:
            table_name = tables[key]
            break

    with open(comparison_log_path, 'a') as file:
        current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        file.write("[" + current_datetime + "] - -----SCRIPT EXECUTION STARTED-----\n")
        file.write("[" + current_datetime + "] - ---FILES TO COMPARE---\n")
        file.write("[" + current_datetime + "] - New file path:" + downloaded_file_path + "\n")
        file.write("[" + current_datetime + "] - Previous most recent file path:" + previous_most_recent_upload_path + "\n")
    
        # Read entire contents of the downloaded file into a dataframe
        df_new = pd.read_csv(downloaded_file_path, delimiter=delimiter, header=header, dtype='object')

        # Left-join the contents of the previous most recent upload
        df_new = df_new.merge(
                pd.read_csv(previous_most_recent_upload_path, delimiter=delimiter, header=header, dtype='object')
                .drop_duplicates(),
                on=list(df_new.columns),
                how='left',
                indicator=True
            )

        # Add a column that specifies whether the row is present in the new file only
        df_new['merge'] = (df_new['_merge'] == 'left_only')

        # Create a new dataframe that consists only of the rows unique to the new file
        df_new = df_new[df_new['_merge'] == 'left_only']

        # Remove the helper columns from the dataframe containing unique records
        df_new = df_new.drop(columns=['_merge', 'merge'])

        # Append the importDate column to the dataframe and populate it with current timestamp
        # df_new['Import Date'] = pd.Timestamp.now()

        # Replace the null values with empty strings instead to avoid integrity errors
        # df_new.fillna('unknown', inplace=True)

        current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        file.write("[" + current_datetime + "] - ---DIFFERENCES COUNT---\n")
        file.write("[" + current_datetime + "] - Unique rows (not present in previous but present in new): " + str(len(df_new)) + "\n")

        downloaded_filename = os.path.basename(downloaded_file_path)

        # Add a column that specifies the source file for the data:
        df_new['sourceFileName'] = downloaded_filename

        # If "differences" directory does not exist in the parent directory, create it
        output_dir = os.path.join(os.path.dirname(downloaded_file_path), 'differences')
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)

        # Save unique rows to a file, if any
        unique_output_path = os.path.join(output_dir, f"unique_{downloaded_filename}")
        if not df_new.empty:
            df_new.to_csv(unique_output_path, index=False)

            current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            file.write("[" + current_datetime + "] - CSV with unique rows only saved in " + unique_output_path + "\n")
        else:
            unique_output_path = None

        # If either file was created successfully, then modify the database accordingly
        if unique_output_path:
            current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            file.write("[" + current_datetime + "] - ---DATABASE MODIFICATIONS---\n")
            if unique_output_path:
                write_data(unique_output_path, table_name)

                current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                file.write("[" + current_datetime + "] - Data from " + unique_output_path + " appended to the " + table_name + " table\n")

            current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            file.write("[" + current_datetime + "] - -----SCRIPT EXECUTION COMPLETED-----\n")

            sys.exit(0)
        else:
            current_datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            file.write("[" + current_datetime + "] - -----SCRIPT EXECUTION COMPLETED-----\n")

            sys.exit(1)

# Run the function
compare_csvs(new_file, previous_file, csv_delimiter, csv_header, comp_log_path)
