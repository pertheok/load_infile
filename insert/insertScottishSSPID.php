<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'SCOT SSPIDS New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X32SSPID_20230214';
        $tableName = 'scot_sspid';
        $importType = 'scot_sspid';
        $dataset = array(
            'SPID', 'ServiceCategory', 'Schedule3', 'ExemptCustomerFlag', 'CustomerClassification', '29e', 'SICCode', 'RateableValue', 'SurfaceArea', 
            'SPIDVacant', 'PropertyDrainage', 'RoadDrainage', 'Unmeasurable', 'SPIDStatus', 'EWA', 'LiveRateableValue', 'RVTransitionFlag', 'FreeDescriptor', 'SubBuildingName', 
            'BuildingName', 'BuildingNumber', 'DependentThoroughfareName', 'DependentThoroughfareDescriptor', 'ThoroughfareName', 'ThoroughfareDescriptor', 'DoubleDependentLocality', 
            'DependentLocality', 'PostTown', 'County', 'Postcode', 'UTCODE', 'NCODE', 'CustomerName', 'OrgId', 'PcentExemption', 'ConnectionDate', 
            'NewConnectionType', 'onsumptionIndicator', 'RegistrationStartDate', 'SAAReferenceNumber', 'SAAReferenceNumberAbsenceCode', 'UPRN', 'UPRNAbsenceCode', 'MTSPID'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>