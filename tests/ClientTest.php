<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\ODMTests;

use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use RuntimeLLC\Mongo\Client;

class ClientTest extends TestCase
{
	protected function setUp(): void
	{

	}

	protected function tearDown(): void
	{

	}

	/**
	 * @expectException InvalidArgumentException
	 * @return void
	 */
	public function testCreateClientWrongDSN()
	{
		$this->expectException(\InvalidArgumentException::class);

		$client = new Client("unknown", "wrong_connection_string");
	}

	public function testCreateClient()
	{
		$client = new Client("unknown", "mongodb://user:user_password@192.0.1.112:27017/test_db");
		$this->assertNotNull($client);
	}

	public function testGetDB()
	{
		$client = new Client("unknown", "mongodb://user:user_password@192.0.1.112:27017/test_db");
		$db = $client->getDatabase();
		$this->assertNotNull($db);
		$this->assertEquals(Database::class, get_class($db));
	}
}

