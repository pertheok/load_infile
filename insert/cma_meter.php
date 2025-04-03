<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'cma_meter New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X33ACTIVE_PENDING_METERS_20230214';
        $tableName = 'cma_meter';
        $importType = 'cma_meter';
        $dataset = array(
            'D3001MeterId', 
            'D2001SPID', 
            'D3002ChargeableMeterSize', 
            'D3003PhysicalMeterSize', 
            'D3004NrDigits', 
            'D3005SewerageChargeableMeterSize', 
            'D3007ReturnToSewerAllowance', 
            'D3011MeterReadFrequency', 
            'D3013MeterMake', 
            'D3014ManufacturerMeterSerialNr', 
            'D3022MeterTreatment', 
            'D2010YVe', 
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
            'D3015DataLoggerSW', 
            'D3016DataLoggerNonSW', 
            'D3017GISX', 
            'D3018GISY', 
            'D3019GISZFreeDescriptor', 
            'D3023AccreditedEntityInstall', 
            'D3025MeterLocationCode'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>