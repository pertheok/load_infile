<?php
	
	
	// Database Connection*/
	
	define('DB_HOST', 'wcrm-mds-dev.mariadb.database.azure.com');
	define('DB_USER', 'chaarmadminuser@wcrm-mds-dev');
	define('DB_PASS', 'vxTWANgZx4u^#Bg$');
	define('DB_NAME', 'mds');
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