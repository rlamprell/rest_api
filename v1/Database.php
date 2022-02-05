<?php
/* This class provides the connection to the mySql server using PDO */
class Database {
	
	// db connection
	private $pdo;
	
	// setup the db connect and check it works
	public function __construct() {
		
		$userName 	= 'sgrlampr';
		$password 	= 'tiggie23';
		$dbName 	= 'sgrlampr';
		$hostName 	= 'studdb.csc.liv.ac.uk';
		
		try {
			
			// db options - using mysql which supports prepared statements
			$options = array(	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
								PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
								PDO::ATTR_EMULATE_PREPARES => false);
			
			// establish db connection using parameters
			$this->pdo = new PDO("mysql:host=$hostName; dbname=$dbName", 
							$userName, $password, $options);
			
		
		}
		// throw an error if the connection fails
		catch (PDOException $err) {

			// throw a 500 (internal server) error if unable to connect
			throw new Exception($err->getMessage(), 500);
		}
	}

	// return the connection
	public function getConnection() {
		
		return $this->pdo;
	}
}
?>