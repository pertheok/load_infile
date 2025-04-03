<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'SCOT SWAPPED DISC READS New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X31WSPID_20230214';
        $tableName = 'scot_wspid';
        $importType = 'scot_wspid';
        $dataset = array(
            'SPID', 'ServiceCategory', 'Schedule3', 'ExemptCustomerFlag', 'CustomerClassification', '29e', 'SICCode', 'RateableValue', 'FarmCroft', 'SPIDVacant', 
            'TroughsDrinkingBowls', 'OutsideTaps', 'Unmeasurable', 'SPIDStatus', 'EWA', 'LiveRateableValue', 'RVTransitionFlag', 'FreeDescriptor', 
            'SubBuildingName', 'BuildingName', 'BuildingNumber', 'DependentThoroughfareName', 'DependentThoroughfareDescriptor', 'ThoroughfareName', 'ThoroughfareDescriptor', 
            'DoubleDependentLocality', 'DependentLocality', 'PostTown', 'County', 'Postcode', 'UTCODE', 'NCODE', 'CustomerName', 'OrgId', 'PcentExemption', 'ConnectionDate', 
            'NewConnectionType', 'onsumptionIndicator', 
            'RegistrationStartDate', 'MeteredBuildingWater', 'AccreditedEntityInstall', 'SAAReferenceNumber', 'SAAReferenceNumberAbsenceCode', 'UPRN', 'UPRNAbsenceCode'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>