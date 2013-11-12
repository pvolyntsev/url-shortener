<?php

class AppPdoConnection {

	protected $connection;

	protected $connectionString;

//	protected $emulatePrepare = true;

	protected $username = '';

	protected $password = '';

	protected $charset = 'utf8';

	protected $driverOptions = array();

	function __construct($config) {
		if (isset($config['db'])) {
			foreach($config['db'] as $key => $value) {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}
		}
	}

	/**
	 * @return PDO
	 */
	public function getConnection() {
		if (is_null($this->connection)) {
			$this->connection = new PDO($this->connectionString, $this->username, $this->password, $this->driverOptions);
		}
		return $this->connection;
	}

	public function getRow($query, $parameters = array(), $mode = PDO::FETCH_ASSOC) {
		$con = $this->getConnection();
		$statement = $con->prepare($query);
		$statement->execute($parameters);
		return $statement->fetch($mode);
	}

	public function execute($query, $parameters = array()) {
		$con = $this->getConnection();
		$statement = $con->prepare($query);
		$result = $statement->execute($parameters);
		if (preg_match('/^\s*INSERT\s/i', $query)) {
			return $con->lastInsertId();
		} else {
			return $result;
		}
	}
}