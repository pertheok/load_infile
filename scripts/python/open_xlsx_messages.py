import sys
import os
import pandas as pd
from read_xlsx_xml_messages import read_xlsx_xml_messages
from convert_column_names import convert_column_names
from write_dataframe import write_dataframe

# filepath = r"C:\Users\Jakub\Downloads\01.10.2025 - 03.11.2025.xlsx"
filepath = sys.argv[1]

def open_xlsx_messages(messages_file_path, output_csv_path=None):

    # Read Excel
    df_in = pd.read_excel(messages_file_path)

    read_messages = []

    for _, row in df_in.iterrows():
        xml_content = row.iloc[5]  # 6th column
        if not isinstance(xml_content, str):
            continue
        if not 'ns0:T105.M_NotifyMeterRead' in xml_content:
            continue

        xml_data = read_xlsx_xml_messages(xml_content)
        read_messages.extend(xml_data)

    # Convert to DataFrame
    df = pd.DataFrame(read_messages)

    if df.empty:
        print("No payload messages found")
        return df

    # Rename meterRead → meterReadValue
    if 'MeterRead' in df.columns:
        df = df.rename(columns={'MeterRead': 'meterReadValue'})

    # Truncate MeterReadDate to date and format as dd-mm-yyyy
    if 'MeterReadDate' in df.columns:
        df['MeterReadDate'] = pd.to_datetime(df['MeterReadDate'], errors='coerce').dt.strftime('%d-%m-%Y')

    # Drop unwanted columns if they exist
    drop_cols = [
        'TextCommentField',
        'EstimatedReadRemedialWorkIndicator',
        'MeterRemovalReason',
        'EstimatedReadReasonCode'
    ]
    df = df.drop(columns=[c for c in drop_cols if c in df.columns])

    # Convert column names to standard naming
    df.columns = convert_column_names(df.columns)

    # Write to CSV if path provided
    if output_csv_path:
        df.to_csv(output_csv_path, index=False, encoding='utf-8-sig')
        print(f"Saved CSV to: {output_csv_path}")
    
    # return df
    write_dataframe(df, 'reads')

open_xlsx_messages(filepath)
# Example usage
# output_csv = r"C:\Users\Jakub\Downloads\01.10.2025 - 03.11.2025_T105M.csv"
# df = open_xlsx_messages(filepath, output_csv_path=output_csv)
# print(df.head())