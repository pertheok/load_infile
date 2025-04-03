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
    include('../includes/databaseDev.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {

        //$fileName = $_GET['filename'];
        $fileName = 'MDS_WSSCO_SMARTA_R_E20230510_G2023051101';
        $tableName = 'mds_wssco';
        $importType = 'mds_wssco';
        $dataset = array(
            'spid',
            'meteredPotableWaterTariffCode',
            'meteredPotableWaterSpecialAgreementFlag',
            'meteredPotableWaterSpecialAgreementFactor',
            'meteredPotableWaterSpecialAgreementReference',
            'meteredPotableWaterMaximumDailyDemand',
            'meteredPotableWaterDailyReservedCapacity',
            'meteredNonPotableWaterTariffCode',
            'meteredNonPotableWaterSpecialAgreementFlag',
            'meteredNonPotableWaterSpecialAgreementFactor',
            'meteredNonPotableWaterSpecialAgreementReference',
            'meteredNonPotableWaterMaximumDailyDemand',
            'meteredNonPotableWaterDailyReservedCapacity',
            'assessedWaterTariffCode',
            'tariffBand',
            'assessedVolumetricRate',
            'assessedChargeableMeterSize',
            'assessedWaterSpecialAgreementFlag',
            'assessedWaterSpecialAgreementFactor',
            'assessedWaterSpecialAgreementReference',
            'unmeasuredWaterTariffCode',
            'unmeasuredWaterSpecialAgreementFlag',
            'unmeasuredWaterSpecialAgreementFactor',
            'unmeasuredWaterSpecialAgreementReference',
            'unmeasuredItemsTypeACount',
            'unmeasuredItemsTypeADescription',
            'unmeasuredItemsTypeBCount',
            'unmeasuredItemsTypeBDescription',
            'unmeasuredItemsTypeCCount',
            'unmeasuredItemsTypeCDescription',
            'unmeasuredItemsTypeDCount',
            'unmeasuredItemsTypeDDescription',
            'unmeasuredItemsTypeECount',
            'unmeasuredItemsTypeEDescription',
            'unmeasuredItemsTypeFCount',
            'unmeasuredItemsTypeFDescription',
            'unmeasuredItemsTypeGCount',
            'unmeasuredItemsTypeGDescription',
            'unmeasuredItemsTypeHCount',
            'unmeasuredItemsTypeHDescription',
            'pipeSize',
            'chargeAdjustmentTariffCode'

        );        

        // Insert to MDS 
        mdsInsert($pdo, $fileName, $tableName, $importType, $dataset);


       
    } catch(PDOException $e) {

        echo 'DB load error: ' . $e->getMessage();

    }

    include('../includes/pageAudit.php');
?>

