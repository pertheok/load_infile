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
        $fileName = 'X39SWAPPED_DISCONTINUED_METER_READS_20230214';
        $tableName = 'scot_swapped_disc_reads';
        $importType = 'scot_swapped_disc_reads';
        $dataset = array(
            'SPID', 'MeterId', 'MeterReadDate', 'MeterRead', 'MeterReadType', 'SReadReasonCode', 'RolloverIndicator', 'RolloverFlag'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>