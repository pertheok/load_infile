import xml.etree.ElementTree as ET

def read_xlsx_xml_messages(xml_content):
    root = ET.fromstring(xml_content)

    # Array to store all valid messages
    data = []

    # Only iterate over PayLoad elements
    for payload in root.findall('.//{*}PayLoad'):
        transaction_data = {}

        # Loop through tags in the payload
        for element in payload.iter():
            if element == payload:
                continue  # skip the PayLoad parent itself

            # Extract transaction type if present in tag
            if element.tag.find('.M_') != -1:
                tag = 'messageType'
                value = element.tag.split('.M_')[-1]
            else:
                tag = element.tag.split('}')[-1]
                value = element.text.strip() if element.text else None

            # Assign value to the tag in transaction_data
            transaction_data[tag] = value

        # Only append if there is actual data
        if transaction_data:
            data.append(transaction_data)

    return data