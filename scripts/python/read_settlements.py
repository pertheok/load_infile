import pandas as pd
import os
from write_dataframe import write_dataframe
from convert_column_names import convert_column_names

def read_settlements(csv_file_paths):

    BASE_DIR = r"\\EC2AMAZ-BKV9RT4\AWS-S3-MDS\SMARTA_R\Settlement\unzipped"
    dataframes = []
    for file_name in csv_file_paths:
        try:
            full_path = os.path.join(BASE_DIR, file_name)
            df = pd.read_csv(full_path)
            df.columns = convert_column_names(df.columns)
            df['sourceFileName'] = file_name
            dataframes.append((os.path.basename(full_path), df))
        except Exception as e:
            print(f"Failed to read {full_path}: {e}")
    
    write_dataframe(dataframes, 'settlements')