<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'cma_mnets New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $fileName = 'X36METER_NETWORKS_20230214';
        $tableName = 'cma_mnets';
        $importType = 'cma_mnets';
        $dataset = array(
            'D2035MainSPID', 
            'D3027MainMeterID', 
            'D2036SubSPID', 
            'D3006SubMeterID', 
            'D4006EffectiveFrom'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

    include('../includes/pageAudit.php');
?>