<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\ODMTests\Resources;

use RuntimeLLC\Mongo\Client;
use RuntimeLLC\Mongo\EntityManager;

abstract class BaseTestManager extends EntityManager
{
	/**
	 *
	 * @return Client
	 */
	public function getClient() : Client
	{
		return new Client($GLOBALS["mongo.dbname"], $GLOBALS["mongo.server"]);
	}
}

