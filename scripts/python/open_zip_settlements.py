import zipfile
import os
import sys
import load_env  # populates os.environ from config/.env
from read_settlements import read_settlements

filepath = sys.argv[1]
destination = os.environ['SETTLEMENTS_UNZIP_DIR']

def open_zip_settlements(filepath, destination):

    if not zipfile.is_zipfile(filepath):
        print(f"The file at {filepath} is not a valid zip file.")
        return

    # Open and read the .zip file contents
    with zipfile.ZipFile(filepath, 'r') as zip_ref:
        zip_ref.extractall(destination)
        print(f"Extracted all files to: {destination}")
        zipped_filenames = zip_ref.namelist()
        print(zipped_filenames)
        
        read_settlements(zipped_filenames)

open_zip_settlements(filepath, destination)
