<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'SCOT AP METERS New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X33ACTIVE_PENDING_METERS_20230214';
        $tableName = 'scot_apmeters';
        $importType = 'scot_apmeters';
        $dataset = array(
            'MeterId', 
            'SPID', 
            'ChargeableMeterSize', 
            'PhysicalMeterSize', 
            'NrDigits', 
            'SewerageChargeableMeterSize', 
            'ReturnToSewerAllowance', 
            'MeterReadFrequency', 
            'MeterMake', 
            'ManufacturerMeterSerialNr', 
            'MeterTreatment', 
            'YVe', 
            'FreeDescriptor',
            'SubBuildingName', 
            'BuildingName', 
            'BuildingNumber', 
            'DependentThoroughfareName', 
            'DependentThoroughfareDescriptor', 
            'ThoroughfareName', 
            'ThoroughfareDescriptor', 
            'DoubleDependentLocality', 
            'DependentLocality', 
            'PostTown', 
            'County', 
            'Postcode', 
            'UTCODE', 
            'NCODE', 
            'CustomerName', 'OrgId', 'DataLoggerSW', 'DataLoggerNonSW', 'GISX', 'GISY', 'GISZFreeDescriptor', 'AccreditedEntityInstall', 'MeterLocationCode'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>