<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\Mongo;

use MongoDB\Database;

class Client extends \MongoDB\Client
{
	protected Database $db;
	protected string $dbName;

	public function __construct($dbName, string $uri, array $uriOptions = [], array $driverOptions = [])
	{
		parent::__construct($uri, $uriOptions, $driverOptions);

		$this->dbName = $dbName;
		$this->db = $this->selectDatabase($dbName);
	}

	/**
	 * @return Database
	 */
	public function getDatabase() : Database
	{
		return $this->db;
	}
}

