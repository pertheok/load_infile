<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'mds_mspid New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $filePath = $argv[1];
        $tableName = 'mds_mspid';
        $dataset = array(
            'meterManufacturer',
            'manufacturerMeterSerialNumber',
            'spid',
            'initialMeterReadDate',
        );        

        // Insert to MDS 
        mdsInsert($pdo, $filePath, $tableName, $dataset);
       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

?>