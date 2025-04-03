<?php
    // Display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Starts script timer
    set_time_limit(10000);
    $timeStart = microtime(true);

    // Site auditing action
    $action = 'mds_tariff New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $fileName = 'MDS_READS_SMARTA_R_E20230510_G2023051101';
        $tableName = 'mds_tariff';
        $dataset = array(
            'WholesalerID',
            'TariffCode',
            'TariffEffectiveFromDate',
            'TariffName',
            'TariffStatus',
            'LegacyTariffEffectiveFromDate',
            'ApplicableServiceComponent',
            'TariffAuthorisationCode',
            'ChargeElementBillingFieldName',
            'ChargeElementBillingField',
            'ChargeElementProperty1',
            'ChargeElementProperty1Value',
            'ChargeElementProperty2',
            'ChargeElementProperty2Value',
            'ChargeElementProperty3',
            'ChargeElementProperty3Value',
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $dataset);
       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

    include('../includes/pageAudit.php');
?>