<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'METER New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'MDS_METER_SMARTA_R_E20230510_G2023051101';
        $tableName = 'mds_meter';
        $importType = 'MDS_METER';
        $dataset = array(
            'meterManufacturer',
            'manufacturerMeterSerialNumber',
            'spid',
            'initialMeterReadDate',
            'meterEffectiveToDate',
            'numberofDigits',
            'measurementUnitsatMeter',
            'measurementUnitsFreeDescriptor',
            'physicalMeterSize',
            'meterReadMinimumFrequency',
            'returntoSewer',
            'waterChargeableMeterSize',
            'sewerageChargeableMeterSize',
            'dataloggerWholesaler',
            'dataloggernonWholesaler',
            'gISX',
            'gISY',
            'meterLocationFreeDescriptor',
            'meterOutreaderGISX',
            'meterOutreaderGISY',
            'meterOutreaderLocationFreeDescriptor',
            'meterTreatment',
            'meterLocationCode',
            'combiMeterFlag',
            'yearlyVolumeEstimate',
            'remoteReadType',
            'remoteSerialNumber',
            'outreaderProtocol',
            'addressFreeDescriptor',
            'addressSecondaryAddressableObject',
            'addressPrimaryAddressableObject',
            'addressAddressLine1',
            'addressAddressLine2',
            'addressAddressLine3',
            'addressAddressLine4',
            'addressAddressLine5',
            'addressPostcode',
            'addressPAFAddressKey'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset);

    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>