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
        $tableName = 'cma_dpids';
        $importType = 'cma_dpids';
        $dataset = [
            'D6001DPID',
            'D2001SPID',
            'D6003CDV',
            'D6004sBODL',
            'D6005TSSL',
            'D6006Ot',
            'D6007St',
            'D6009NonDomesticAllowance',
            'D6010SDTIndicator',
            'D6011TETreatment',
            'D6012PcentAllowance',
            'D6013FixedAllowance',
            'D2003Schedule3',
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
        ];        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, '|');
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>