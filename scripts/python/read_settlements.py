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
            print(full_path)
            df = pd.read_csv(full_path, dtype='object')
            df.columns = convert_column_names(df.columns)
            df['sourceFileName'] = file_name
            dataframes.append((os.path.basename(full_path), df))
        except Exception as e:
            print(f"Failed to read {full_path}: {e}")
    
    # Filter out empty DataFrames before concat
    all_dfs = [df for _, df in dataframes if not df.empty]

    if all_dfs:  # only concat if there's something to concat
        combined_df = pd.concat(all_dfs, ignore_index=True)
        write_dataframe(combined_df, 'settlements')
    else:
        print("No valid dataframes to concatenate.")