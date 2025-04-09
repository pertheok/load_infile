<?php

    function mdsInsert($pdo, $filePath, $tableName, $dataset, $separator = ',', $lines = 2) 
    {
        $dataset = array_map('trim', $dataset);
        $columns = [];
        $setClauses = [];

        foreach ($dataset as $column) {
            $columns[] = "@{$column}";
            $setClauses[] = "{$column} = NULLIF(@{$column}, '')";
        }

        $columnList = implode(', ', $columns);
        $setClause = implode(', ', $setClauses);

        $filePath = addslashes($filePath);

        $query = "LOAD DATA LOCAL INFILE '" . $filePath . "' INTO TABLE " . $tableName . "
            FIELDS TERMINATED BY '" . $separator . "' OPTIONALLY ENCLOSED BY '\"' 
            LINES TERMINATED BY '\\n' 
            IGNORE " . $lines . " LINES 
            (" . $columnList . ") 
            SET " . $setClause;

        $pdo->exec("TRUNCATE TABLE $tableName");
        $pdo->exec($query);

        return true;
    }
?>