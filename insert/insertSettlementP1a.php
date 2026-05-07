<?php
     // Display errors
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);
     
     // Starts script timer
     set_time_limit(10000);
     $timeStart = microtime(true);

    // Site auditing action
    $action = 'TBA';

    // include required files
    include('../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'A_UNITED_W_SMARTA_R_2018_07_R1';
        $tableName = 'mds_settlement_p1_a';
        $importType = 'mds_settlement_p1_a';
        $dataset = array(
            'Wholesaler',
            'Retailer',
            'Run',
            'Period',
            'Timestamp',
            'Component',
            'Tariff',
            'Name',
            'Number',
            'Days',
            'Volume',
            'V_Charge',
            'NV_Charge',
            'Test'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, null, 1);
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

   
?>