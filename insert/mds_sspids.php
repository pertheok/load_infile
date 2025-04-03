<?php
   // Display errors
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
   // Starts script timer
   set_time_limit(10000);
   $timeStart = microtime(true);

    // Site auditing action
    $action = 'SSPIDS New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'MDS_SSPID_SMARTA_R_E20221110_G2022111100';
        $tableName = 'mds_sspids';
        $importType = 'mds_sspids';
        $dataset = array(
            'spid',
            'wholesalerID',
            'retailerID',
            'otherWholesalerID',
            'multipleWholesalersFlag',
            'disconnectionStatus',
            'vOABAReference',
            'vOABAReferenceReasonCode',
            'uPRN',
            'uPRNReasonCode',
            'customerClassificationSensitiveCustomer',
            'publicHealthRelatedSiteSpecificArrangementsFlag',
            'nonPublicHealthRelatedSiteSpecificArrangementsFlag',
            'nonPublicHealthRelatedSiteSpecificArrangementsFreeDescriptor',
            'standardIndustrialClassificationCode',
            'standardIndustrialClassificationCodeType',
            'rateableValue',
            'occupancyStatus',
            'buildingWaterStatus',
            'landlordSPID',
            'pairingReference',
            'pairingReferenceReasonCode',
            'otherSPID',
            'section154ADwellingUnits',
            'customerName',
            'customerBannerName',
            'premisesAddressFreeDescriptor',
            'premisesAddressSecondaryAddressableObject',
            'premisesAddressPrimaryAddressableObject',
            'premisesAddressAddressLine1',
            'premisesAddressAddressLine2',
            'premisesAddressAddressLine3',
            'premisesAddressAddressLine4',
            'premisesAddressAddressLine5',
            'premisesAddressPostcode',
            'premisesAddressPAFAddressKey',
            'supplyPointEffectiveFromDate',
            'registrationStartDate',
            'otherServiceCategoryProvidedFlag',
            'otherServiceCategoryProvidedFlagReason',
            'volumeTransferType',
            'interimDutySupplyPointFlag'
        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset);

        
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

?>

