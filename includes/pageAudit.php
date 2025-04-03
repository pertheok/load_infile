<?php
    $timeEnd = microtime(true);

    $executionTime = ($timeEnd - $timeStart);

    //execution time of the script
    echo '<b>Total Execution Time:</b> '.$executionTime.' seconds';

    $scriptPath = $_SERVER['SCRIPT_NAME'];

    $data = [
        'a_page' => $scriptPath,
        'a_page_load_time' => $executionTime,
        'a_action' => $action
    ];

    $sql = 'INSERT INTO site_audit (a_page, a_page_load_time, a_action) VALUES (:a_page, :a_page_load_time, :a_action)';

    $stmt = $pdo->prepare($sql);                
    $stmt->execute($data);
?>