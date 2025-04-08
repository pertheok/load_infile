<?php
    set_time_limit(10000);
    $timeStart = microtime(true); 

    // Site auditing action
    $action = 'mds_sssco New Uploads';

    // include required files
    include('../includes/functions.php');
    include('../includes/database.php');
    
    // Using LOAD INFILE we can insert each row from the CSV without having to store in variables we insert directly to the database.
    try
    {
        $filePath = $argv[1];
        $tableName = 'mds_sssco';
        $dataset = array(
            'spid',
            'meteredSewerageTariffCode',
            'meteredSewerageSpecialAgreementFlag',
            'meteredSewerageSpecialAgreementFactor',
            'meteredSewerageSpecialAgreementReference',
            'assessedSewerageTariffCode',
            'tariffBand',
            'assessedVolumetricRate',
            'assessedChargeableMeterSize',
            'assessedSewerageSpecialAgreementFlag',
            'assessedSewerageSpecialAgreementFactor',
            'assessedSewerageSpecialAgreementReference',
            'unmeasuredSewerageTariffCode',
            'unmeasuredSewerageSpecialAgreementFlag',
            'unmeasuredSewerageSpecialAgreementFactor',
            'unmeasuredSewerageSpecialAgreementReference',
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
            'chargeAdjustmentTariffCode',
            'surfaceWaterTariffCode',
            'areaDrained',
            'communityConcessionFlag',
            'surfaceWaterDrainageChargeAdjustmentFactor',
            'surfaceWaterSpecialAgreementFlag',
            'surfaceWaterSpecialAgreementFactor',
            'surfaceWaterSpecialAgreementReference',
            'highwayDrainageTariffCode',
            'surfaceArea',
            'communityConcessionFlag1',
            'highwayDrainageWaterSpecialAgreementFlag',
            'highwayDrainageWaterSpecialAgreementFactor',
            'highwayDrainageWaterSpecialAgreementReference'

        );        

        // Insert to MDS 
        mdsInsert($pdo, $filePath, $tableName, $dataset);


       
    } catch(PDOException $e) {
        echo 'DB load error: ' . $e->getMessage();
    }

    // // include('../includes/pageAudit.php');
?>

