<?php
    // Display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Starts script timer
    set_time_limit(10000);
    $timeStart = microtime(true);

    // Site auditing action
    $action = 'WSPIDS New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'MDS_WSPID_SMARTA_R_E20230510_G2023051101';
        $tableName = 'mds_wspid';
        $importType = 'mds_wspid';
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

        // Now run query to compare the new_upload and the current table, update them so the current database reflects the new data, either new rows or updates
        // FLAW -- Carries on incrementing row count in mysql if updated, doesn't cause issues
        $query = '
            INSERT INTO
            mds_dchvl
                (dpid, spid, effectiveFromDate, calculatedDischargeId, effectiveToDate, calculatedDischargeNotifiedVolume)
                
                SELECT 
                    dpid, spid, effectiveFromDate, calculatedDischargeId, effectiveToDate, calculatedDischargeNotifiedVolume
                FROM 
                    mds_dchvl_new_upload a        
                ON DUPLICATE KEY UPDATE
                    mds_dchvl.calculatedDischargeId = a.calculatedDischargeId,
                    mds_dchvl.effectiveToDate = a.effectiveToDate, 
                    mds_dchvl.calculatedDischargeNotifiedVolume = a.calculatedDischargeNotifiedVolume            
        ';
       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>

