import xml.etree.ElementTree as ET

def read_xml_messages(xml_content):
    root = ET.fromstring(xml_content)
    namespace = {
        'soapenv': 'http://schemas.xmlsoap.org/soap/envelope/',
        'ns0': 'urn:co.uk:mosl:cmos:b2b:v01',
        'ns1': 'urn:co.uk:mosl:cmos:b2b:message:v01'
    }
    
    # Find the Transaction elements
    transactions = root.findall('.//ns1:Transactions', namespaces=namespace)[0]

    # Declare the array that will store all the valid messages within the file
    data = []

    # Loop through transactions within the file
    for transaction in transactions:
        transaction_data = {}

        # Loop through tags in the transaction
        for element in transaction.iter():
            
            # Skip the parent element itself
            if element == transaction:
                continue

            # Exclude the grouping tags that don't contain the data themselves
            if element.tag.endswith('}Transaction') or \
            element.tag.endswith('}Header') or \
            element.tag.endswith('}PayLoad'):
                continue
        
            # Extract the transaction type which is being kept in the tag name
            if element.tag.find('.M_') != -1:
                tag = 'messageType'
                value = element.tag.split('.M_')[-1]
            else:
                # Split the tag to get the local name, then get its value
                tag = element.tag.split('}')[-1]
                value = element.text.strip() if element.text else None

            # Assign the value to the tag within the transaction object
            transaction_data[tag] = value

        # Append the transaction object into the array containing all valid messages within the file
        data.append(transaction_data)

    return data