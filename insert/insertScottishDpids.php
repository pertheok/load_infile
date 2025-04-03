<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'SCOT DPIDS New Uploads';

    // include required files
    include('../../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'X34DPIDS_20230214';
        $tableName = 'scot_dpids';
        $importType = 'scot_dpids';
        $dataset = array(
            'DPID','SPID','CDV','sBODL','TSSL','Ot','St','NondomesticAllowance','SDTIndicator','TETreatment','PcentAllowance','FixedAllowance','Schedule3',
            'FreeDescriptor','SubBuildingName','BuildingName','BuildingNumber','DependentThoroughfareName','DependentThoroughfareDescriptor',
            'ThoroughfareName','ThoroughfareDescriptor','DoubleDependentLocality','DependentLocality','PostTown','County','Postcode','UTCODE','NCODE','CustomerName','OrgId'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>