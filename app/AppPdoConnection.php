<?php

/**
 * Class provides lasy initialization of database connection and some query methods
 */
class AppPdoConnection {

	/**
	 * @var PDO
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $connectionString;

	/**
	 * @var string
	 */
	protected $username = '';

	/**
	 * @var string
	 */
	protected $password = '';

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
