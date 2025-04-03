def convert_column_names(column_names):
    converted_names = []
    for name in column_names:
        name_modified = ''.join(char for char in name if char.isalnum())  # Remove non-alphanumeric characters and join the result together
        converted = name_modified[0].lower() + name_modified[1:]
        converted_names.append(converted)
    return converted_names