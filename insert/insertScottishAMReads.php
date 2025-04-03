<?php
    // Display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Starts script timer
    set_time_limit(10000);
    $timeStart = microtime(true);

    // Site auditing action
    $action = 'SCOT AM READS New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X35ACTIVE_METER_READS_20230511';
        $tableName = 'scot_amreads';
        $importType = 'scot_amreads';
        $dataset = array(
            'SPID', 
            'MeterId', 
            'MeterReadDate', 
            'MeterRead', 
            'MeterReadType', 
            'SReadReasonCode', 
            'RolloverIndicator', 
            'RolloverFlag'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>