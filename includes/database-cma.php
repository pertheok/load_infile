<?php
	
	// Database Connection
	
	define('DB_HOST', 'singleview-smarta-production.cbi7axo6juj4.eu-west-2.rds.amazonaws.com:3306');
	define('DB_USER', 'admin');
	define('DB_PASS', 'WkZe8euII9BhqGT8kSxW4mde6D8A8GAc');
	define('DB_NAME', 'cma-current');
	 // Character set
	define('DB_CHARSET', 'utf8mb4');

	$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
	  
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