<?php

    function mdsInsert($pdo, $filePath, $tableName, $dataset, $separator = ',', $lines = 2) 
    {
        // split by commas
        $dataset = implode(',', $dataset);

        // Insert to the apporpriate new upload table and the archive table        
        $query = "LOAD DATA LOCAL INFILE '" . $filePath  . "' INTO TABLE " . $tableName . "
                FIELDS TERMINATED BY '" . $separator . "' OPTIONALLY ENCLOSED BY '\"' 
                LINES TERMINATED BY '\\n' 
                IGNORE " . $lines . " LINES 
                (
                    " . $dataset . "
                )";

        $pdo->exec("TRUNCATE TABLE $tableName"); 
        $pdo->exec($query);

        echo '<p>Inserted to ' . $tableName . '</p>';

        return true;
    }
?>