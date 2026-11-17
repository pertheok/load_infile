<?php
	
	
	// Database Connection - credentials come from config/.env
	require_once __DIR__ . '/env.php';

	define('DB_HOST', env('DB_HOST'));
	define('DB_PORT', env('DB_PORT', '3306'));
	define('DB_USER', env('DB_USER'));
	define('DB_PASS', env('DB_PASS'));
	define('DB_NAME', env('DB_NAME', 'mds-current'));
	 // Character set
	define('DB_CHARSET', 'utf8mb4');

	$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
	  
	$opt = [
	    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
	    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
	    \PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$pdo = new \PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_LOCAL_INFILE=>true));

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
?>