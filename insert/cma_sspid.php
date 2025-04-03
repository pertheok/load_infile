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
        $tableName = 'cma_sspid';
        $importType = 'cma_sspid';
        $dataset = array(
            'D2001SPID', 
            'D2002ServiceCategory',
            'D2003Schedule3', 
            'D2004ExemptCustomerFlag', 
            'D2005CustomerClassification', 
            'D200629e', 
            'D2008SICCode', 
            'D2012SurfaceArea', 
            'D2015SPIDVacant', 
            'D2016PropertyDrainage', 
            'D2017RoadDrainage', 
            'D2024Unmeasurable', 
            'D2025SPIDStatus', 
            'D2026EWA', 
            'D2042LiveRateableValue', 
            'D5001FreeDescriptor', 
            'D5002SubBuildingName', 
            'D5003BuildingName', 
            'D5004BuildingNumber', 
            'D5005DependentThoroughfareName', 
            'D5006DependentThoroughfareDescriptor', 
            'D5007ThoroughfareName', 
            'D5008ThoroughfareDescriptor', 
            'D5009DoubleDependentLocality', 
            'D5010DependentLocality', 
            'D5011PostTown', 
            'D5012County', 
            'D5013Postcode', 
            'OUTCODE', 
            'INCODE', 
            'D2027CustomerName', 
            'D4001OrgId', 
            'D2041PcentExemption', 
            'D2013ConnectionDate', 
            'D2023NewConnectionType', 
            'D4002RegistrationStartDate', 
            'D2037SAAReferenceNumber', 
            'D2038SAAReferenceNumberAbsenceCode', 
            'D2039UPRN', 
            'D2040UPRNAbsenceCode', 
            'D2045MTSPID',
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>