<?php

    function mdsInsert($pdo, $fileName, $tableName, $importType, $dataset, $separator = null, $lines = 2) 
    {
        $fileName = '../newdata/' . $fileName . '.csv';

        // split by commas
        $dataset = implode(',', $dataset);

        // if not seperator use a comma else
        if(is_null($separator))
        {
            $separator = ',';
        }

        // Insert to the apporpriate new upload table and the archive table        
        $query = "LOAD DATA LOCAL INFILE '" . $fileName  . "' INTO TABLE " . $tableName . "
                FIELDS TERMINATED BY '" . $separator . "' OPTIONALLY ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n' 
                IGNORE " . $lines . " LINES 
                (
                    " . $dataset . "
                )";

        $pdo->exec($query);
        echo '<p>Inserted to ' . $tableName . '</p>';

        // Insert in to the archive
        $query = "LOAD DATA LOCAL INFILE '" . $fileName  . "' INTO TABLE mds_archive
                FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n' 
                IGNORE 2 LINES 
                (
                " . $dataset . "
                )
                    SET importType = '" . $importType . "'   
                ";

        $pdo->exec($query);
        echo '<p>Inserted to MDS_ARCHIVE</p>';

        return true;
    }
?>