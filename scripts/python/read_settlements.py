import pandas as pd
import os
from write_dataframe import write_dataframe
from convert_column_names import convert_column_names

def read_settlements(csv_file_paths):

    dataframes = []
    for file_path in csv_file_paths:
        try:
            df = pd.read_csv(file_path)
            df.columns = convert_column_names(df.columns)
            df['sourceFileName'] = file_path
            dataframes.append((os.path.basename(file_path), df))
        except Exception as e:
            print(f"Failed to read {file_path}: {e}")
    
    write_dataframe(df, 'settlements')