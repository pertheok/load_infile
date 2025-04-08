<?php
    // Display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Starts script timer
    set_time_limit(10000);
    $timeStart = microtime(true);

    // Site auditing action
    $action = 'cma_reads New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $filePath = $argv[1];
        $tableName = 'cma_reads';
        $dataset = array(
            'D2001SPID', 
            'D3001MeterId', 
            'D3009MeterReadDate', 
            'D3008MeterRead', 
            'D3010MeterReadType', 
            'D3028SReadReasonCode', 
            'D3020RolloverIndicator', 
            'D3021RolloverFlag'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $filePath, $tableName, $dataset);
       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

    // include('../includes/pageAudit.php');
?>