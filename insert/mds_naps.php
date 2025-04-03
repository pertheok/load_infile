<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'MNETS New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'MDS_MNETS_SMARTA_R_E20230510_G2023051101';
        $tableName = 'mds_naps';
        $importType = 'mds_naps';
        $dataset = array(
            'spid',
            'serviceCategory',
            'spidStatus',
            'dateOfLastChangeOnSpid',
            'supplyPointEffectiveFromDate',
            't107WSubmissionDateAndTime',
            'latePartialRegistrationApplication',
            'retailerId',
            'wholesalerId',
            't102MNotificationDateAndTime',
            't103RSubmissionDateAndTime',
            'occupancyStatus',
            't112RTCORR112REffectiveFromDate',
            't112RTCORR112RSubmissionDateAndTime',
            'customerName',
            'customerBannerName',
            't132REffectiveFromDate',
            't132RSubmissionDateAndTime',
            't139WSubmissionDateAndTime',
            't140WSubmissionDateAndTime',
            'meteredServiceComponent',
            'initialMeterReadDate',
            't104WSubmissionDateAndTime',
            't121WEffectiveFromDate',
            't121WSubmissionDateAndTime',
            'newConnectionType',
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset);
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>