<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'cma_mdpid New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $fileName = 'X37METER_DPID_ASSOCIATIONS_20230214';
        $tableName = 'cma_mdpid';
        $importType = 'cma_mdpid';
        $dataset = array(
            'D2001SPID',
            'D6001DPID',
            'D3001MeterId',
            'D3024MDVol',
            'D4006EffectiveFrom',
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

    include('../includes/pageAudit.php');
?>