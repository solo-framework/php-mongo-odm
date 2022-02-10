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
use DateTime;
use DateTimeInterface;
use Exception;
use JetBrains\PhpStorm\Pure;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;
use PHPUnit\Framework\TestCase;
use RuntimeLLC\Mongo\DataSet;
use RuntimeLLC\Mongo\EntityManager;
use RuntimeLLC\ODMTests\Resources\AddressEntity;
use RuntimeLLC\ODMTests\Resources\BadManager;
use RuntimeLLC\ODMTests\Resources\ExampleManager;
use RuntimeLLC\ODMTests\Resources\FinManager;
use RuntimeLLC\ODMTests\Resources\JustStruct;
use RuntimeLLC\ODMTests\Resources\OrgEntity;
use RuntimeLLC\ODMTests\Resources\PersonEntity;
use RuntimeLLC\ODMTests\Resources\PersonManager;
use RuntimeLLC\ODMTests\Resources\RootEntity;
use RuntimeLLC\ODMTests\Resources\RootManager;

class EntityManagerTest extends TestCase
{
	public ?ExampleManager $manager = null;
	public PersonManager $pm;

	protected PersonEntity $person;

	protected function setUp(): void
	{
		$this->manager = new ExampleManager();
		$this->pm = new PersonManager();

		// load js code
		$initCodeSrc = file_get_contents("./tests/db.js");
		$code = new Javascript($initCodeSrc);
		$this->pm->getClient()->getManager()->executeCommand(
			$GLOBALS["mongo.dbname"],
			new Command(["eval" => $code])
		);
	}

	/**
	 * @param string $name
	 * @param string $password
	 * @param int[] $ints
	 * @param int $age
	 *
	 * @return PersonEntity
	 * @throws Exception
	 */
	private function createPerson($name = "test name", $password="password", $ints=[1,2], $age = 33): PersonEntity{
		$dt = new DateTime("now", new \DateTimeZone("America/Detroit"));
		$ent = new PersonEntity();
		$ent->ignoreField = [1, 2, 3];
		$ent->name = $name;
		$ent->password = $password;
		$ent->date = new UTCDateTime($dt);
		$ent->ints = $ints;
		$ent->age = $age;
		return $this->pm->save($ent);
	}

	#[Pure] private function newComplex(): RootEntity
	{
		$root = new RootEntity();
		$root->name = "RootEntity";
		$root->year = "2000";

		for($i = 0; $i < 3; $i++)
		{
			$org = new OrgEntity();
			$org->inn = "1234567890{$i}";
			$org->ogrn = "999999999{$i}";

			$address = new AddressEntity();
			$address->building = "1{$i}";
			$address->street = "John Lennon";

			$struct = new JustStruct();
			$struct->data = ["param" => "param{$i}", "value" => $i];
			$struct->status = "STATUS_{$i}";

			$address->someStruct = $struct;
			$org->address = $address;
			$root->orgs[] = $org;
		}

		return $root;
	}

	private function createComplex(): RootEntity
	{
		$rm = new RootManager();
		return $rm->save($this->newComplex());
	}

	protected function tearDown(): void
	{
		$this->manager->getClient()->getDatabase()->dropCollection("odm_person");
		$this->manager->getClient()->getDatabase()->dropCollection("odm_example");
		$this->manager->getClient()->getDatabase()->dropCollection("root_collection");
		$this->manager->getClient()->getDatabase()->dropCollection("payment");
	}

	public function testAggregate()
	{
		$pipeline = [
			// все платежи пользователя за текущий месяц
			['$match' => [
				"providerId" => ["\$in" => ["1144"]],
				"status" => "SUCCEED",
				"createDate" => ["\$gte" => 1644475000]
			]],

			['$project' => [
				"amount"=> true,
				"count" => ['$add' => [1]]
			]],

			['$group' => [
				'_id' => null,
				'total' => ['$sum' => '$count'],
				'amount' => ['$sum' => '$amount'],
			]],
		];

		$fm = new FinManager();

		// Можно так
		$res = $fm->aggregate($pipeline);
		// TEST
		$this->assertNotNull($res);
		$this->assertInstanceOf(Cursor::class, $res);

		$data = $res->toArray();
		$this->assertCount(1, $data);
		$this->assertTrue($data[0] instanceof BSONDocument);
		$this->assertEquals(10, $data[0]["amount"]);

		// а можно и так
		$res = $fm->getCollection()->aggregate($pipeline);

		$this->assertNotNull($res);

		$this->assertInstanceOf(Cursor::class, $res);
		$data = $res->toArray();
		$this->assertCount(1, $data);
		$this->assertTrue($data[0] instanceof BSONDocument);
		$this->assertEquals(10, $data[0]["amount"]);
	}

	public function testSaveExt()
	{
		$root = $this->newComplex();
		$saved = $this->createComplex();

		$testStruct = new JustStruct();
		$testStruct->status = "STATUS_1";
		$testStruct->data = ["param" => "param1", "value" => 1];

		// TEST
		$this->assertEquals("9999999991", $saved->orgs[1]->ogrn);
		$this->assertEquals(null, $saved->orgs[1]->id);
		$this->assertEquals(
			$testStruct,
			$saved->orgs[1]->address->someStruct
		);


		// remove for comparsion
		$saved->id = null;
		$this->assertEquals($root, $saved);
	}

	public function testFindOneExt()
	{
		$created = $this->createComplex();

		$rm = new RootManager();
		// db.getCollection('root_collection').find({"orgs": {"$elemMatch":  {"ogrn": "9999999991"}}})
		$saved = $rm->findOne(['orgs' => ['$elemMatch' => ['ogrn' => '9999999991']]]);
		// TEST
		$this->assertEquals($created, $saved);
		$this->assertInstanceOf(RootEntity::class, $saved);
	}

	public function testUpdateBySaveExt()
	{
		$new = $this->createComplex();
		$new->orgs[0]->address->building = "44";
		$new->orgs[1]->address->someStruct->status = "NEW_STATUS";

		$rm = new RootManager();
		$updated = $rm->save($new);

		$saved = $rm->findById($new->id);
		// TEST
		$this->assertEquals($updated, $saved);
		$this->assertInstanceOf(RootEntity::class, $saved);
	}

	public function testGetCollection()
	{
		$coll = $this->manager->getCollection();
		$this->assertNotNull($coll);
	}

	public function testConstructor()
	{
		$this->expectException(\RuntimeException::class);
		$bm = new BadManager();
	}

	public function testFindByIdNotFound()
	{
		$strId = "5cb6cf1440f72c0001746242";
		$saved = $this->manager->findById($strId);
		$this->assertNull($saved);
	}

	public function testSave()
	{
		$ent = new PersonEntity();
		$ent->name = "some name";
		$ent->password = "password";

		$saved = $this->pm->save($ent);

		$this->assertNotNull($saved);
		$this->assertInstanceOf(PersonEntity::class, $saved, "type mismatch");

		$this->assertNotNull($saved->id);
		$this->assertInstanceOf(ObjectId::class, $saved->id, "ObjectId type mismatch");
	}

	/**
	 * @throws Exception
	 */
	public function testFindById()
	{
		$dt = new DateTime("now", new \DateTimeZone("America/Detroit"));

		$pm = new PersonManager();

		$ent = new PersonEntity();
		$ent->name = "testFindById name";
		$ent->password = "testFindById password";
		$ent->date = new UTCDateTime($dt);
		$ent->ints = [1, "str"];
		$ent->age = 33;

		$new = $pm->save($ent);

		// TEST
		$this->assertNotNull($new->id, "Id can't be null");

		/** @var $saved PersonEntity */
		$saved = $pm->findById($new->id);

		// TEST
		$this->assertNotNull($saved);
		$this->assertInstanceOf(PersonEntity::class, $saved);

		// MongoDB saves Date in UTC timezone
		$dt->setTimezone(new \DateTimeZone("Etc/UCT"));

		// TEST
		$this->assertEquals(
			$dt->format(DateTimeInterface::ISO8601),
			$saved->date->toDateTime()->format(DateTimeInterface::ISO8601),
			"DateTime assert failed"
		);

		// TEST
		$this->assertEquals([1, "str"], $saved->ints, "Arrays are not equals");
		// TEST
		$this->assertEquals("testFindById name", $saved->name, "Name is not equals");
		// TEST
		$this->assertEquals(33, $saved->age, "Age is not equals");

	}

	/**
	 * @throws Exception
	 */
	public function testUpdateBySave()
	{
		$saved = $this->createPerson(name: "update");

		// Update field
		$saved->age = 500;
		$saved->name = "after update";

		/** @var $savedAgain PersonEntity */
		$savedAgain = $this->pm->save($saved);

		/** @var $person PersonEntity */
		$person = $this->pm->findById($saved->id);

		$this->assertInstanceOf(PersonEntity::class, $person);
		$this->assertInstanceOf(PersonEntity::class, $savedAgain);
		$this->assertEquals($savedAgain->id, $savedAgain->id);
		$this->assertEquals($savedAgain->age, $person->age);
		$this->assertEquals($saved->id, $person->id);
		$this->assertEquals($saved->name, $person->name);
	}

	/**
	 * @throws Exception
	 */
	public function testRemoveById() : void
	{
		$person = $this->createPerson();
		$this->pm->removeById($person->id);

		$stored = $this->pm->findById($person->id);
		// TEST
		$this->assertNull($stored);
	}


	public function testRemoveByCondition() : void
	{
		$this->createPerson(name: "remove");
		$this->createPerson(name: "remove");

		$res = $this->pm->remove(["name" => "remove"]);

		$this->assertEquals(2, $res->getDeletedCount());

		$stored = $this->pm->find(["name" => "remove"]);
		// TEST
		$this->assertCount(0, $stored->getValues());
	}

	public function testFindNotFound() : void
	{
		$stored = $this->pm->find(["name" => "not_existing"]);

		// TEST
		$this->assertTrue($stored instanceof DataSet);
		$this->assertEquals([], $stored->getValues());
	}

	public function testUpdateById() : void
	{
		$person = $this->createPerson(age: 100);

		$this->pm->updateById(
			$person->id,
			[
				'$set' => ["name" => "Mary"],
				'$inc' => ['age' => 1]
			]
		);

		/** @var $stored PersonEntity */
		$stored = $this->pm->findById($person->id);
		// TEST
		$this->assertNotNull($stored);
		$this->assertEquals("Mary", $stored->name);
		$this->assertEquals(101, $stored->age);
		$this->assertInstanceOf(PersonEntity::class, $stored);
	}

	public function testUpdate()
	{
		$this->createPerson(name: "Anna", age: 31);
		$this->createPerson(name: "Anna", age: 44);

		$this->pm->update(
			["name" => "Anna"],
			['$set' => ["name" => "Mary", "age" => 55]]
		);

		$persons = $this->pm->find(["name" => "Mary"]);

		// TEST
		foreach ($persons as $person)
		{
			$this->assertInstanceOf(PersonEntity::class, $person);
			$this->assertEquals(55, $person->age);
		}
	}

	public function testFindOne()
	{
		$this->createPerson(name: "Anna", age: 30);
		$this->createPerson(name: "Mary", age: 31);

		$res = $this->pm->findOne(["name" => "Anna"]);

		// TEST
		$this->assertTrue($res instanceof PersonEntity);
		$this->assertInstanceOf(PersonEntity::class, $res);
	}

	public function testFindOneNotFound()
	{
		$saved = $this->pm->findOne(["name" => "NotFound"]);
		// TEST
		$this->assertNull($saved, "Entity must be NULL");
	}


	public function testCount()
	{
		$this->createPerson(name: "Anna", age: 30);
		$this->createPerson(name: "Anna", age: 31);

		$res = $this->pm->count(["name" => "Anna"]);

		$this->assertEquals(2, $res);
	}

	public function testBuildObjectIdList()
	{
		$ids = PersonManager::buildObjectIdList([
			"51486d47c674d9fbd71ac4b5",
			"51486d47c674d9fbd71ac4b6",
		]);

		$expected =[
			new ObjectId("51486d47c674d9fbd71ac4b5"),
			new ObjectId("51486d47c674d9fbd71ac4b6")
		];

		// TEST
		$this->assertEquals($expected, $ids);
		$this->assertEquals([], EntityManager::buildObjectIdList([]));
	}

	public function testFetchField()
	{
		$person = $this->createPerson(name: "Alice", age: 32);

		$name = $this->pm->fetchField(["name" => "Alice"], "name");
		// TEST
		$this->assertEquals("Alice", $name);

		$id = $this->pm->fetchField(["_id" => $person->id], "_id");
		// TEST
		$this->assertTrue($id instanceof ObjectId);

		$res = $this->pm->fetchField(["age" => 1000], "non_exists_field");
		// TEST
		$this->assertNull($res);

		$name = $this->pm->fetchField(["name" => "NotFoundName"], "name");
		// TEST
		$this->assertNull($name);
	}

	/**
	 * @throws Exception
	 */
	public function testFetchColumn()
	{
		$this->createPerson(name: "Alice", age: 32);
		$this->createPerson(name: "Bob", age: 34);
		$this->createPerson(name: "Carl", age: 35);

		$names = $this->pm->fetchColumn([], "name");

		$this->assertEquals(["Alice", "Bob", "Carl"], $names);

		$names = $this->pm->fetchColumn(["age" => ['$gt' => 33 ]], "name");
		$this->assertEquals(["Bob", "Carl"], $names);
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function testFetchColumnFail()
	{
		$this->expectException(exception: \MongoDB\Driver\Exception\InvalidArgumentException::class);
		$this->pm->fetchColumn([], "");
	}

	/**
	 * @throws Exception
	 */
	public function testFetchPartEntity()
	{
		$this->createPerson(name: "Petr");
		$part = $this->pm->fetchPartEntity(["name" => "Petr"], ["age", "password"]);

		$this->assertInstanceOf(PersonEntity::class, $part);

		// TEST
		$this->assertEquals([], $part->ints, "ints must be []");
		$this->assertEquals(null, $part->date, "date must be null");
		$this->assertEquals(null, $part->name, "name must be null");
		$this->assertEquals("password", $part->password);
		$this->assertEquals(33, $part->age);

		// TEST
		$part = $this->pm->fetchPartEntity(["name" => "NOT_EXISTS"], ["age", "password"]);
		$this->assertNull($part);
	}

	/**
	 * @throws Exception
	 */
	public function testFindOneAndReplace()
	{
		$this->createPerson(name: "Petr");

		// Replaces the whole documents
		$saved = $this->pm->findOneAndReplace(
			["name" => "Petr"],
			["age" => 222, "name" => "Carl II"]
		);

		$this->assertEquals("Carl II", $saved->name);
		$this->assertEquals(222, $saved->age);
		$this->assertEquals([], $saved->ints, "ints must be []");

		// No document found
		$saved = $this->pm->findOneAndReplace(
			["name" => "Petr_NO_FOUND"],
			["age" => 111, "name" => "Carl"]
		);

		$this->assertNull($saved);

	}

	/**
	 * @throws Exception
	 */
	public function testFindOneAndUpdate()
	{
		$this->createPerson(name: "Petr");
		$saved = $this->pm->findOneAndUpdate(
			["name" => "Petr"],
			['$set' => ["age" => 111, "name" => "Carl"]]
		);

		$this->assertEquals("Carl", $saved->name);
		$this->assertEquals(111, $saved->age);
		$this->assertEquals([1, 2], $saved->ints, "ints must be []");
		$this->assertInstanceOf(PersonEntity::class, $saved);

		// No document found
		$saved = $this->pm->findOneAndUpdate(
			["name" => "Petr_NO_FOUND"],
			['$set' => ["age" => 111, "name" => "Carl"]]
		);

		$this->assertNull($saved);
	}

	public function testFindOneAndUpdateFail()
	{
		$this->expectException(exception: \MongoDB\Driver\Exception\InvalidArgumentException::class);
		$this->expectErrorMessage("Expected an update document with operator as first key or a pipeline");

		$this->createPerson(name: "Petr");
		$saved = $this->pm->findOneAndUpdate(
			["name" => "Petr"],
			["age" => 111] // we should use $set or $inc, etc.

		);
	}


//	public function testLolo()
//	{

//		$lolo = new Lolo();
//		$lolo->inn = "DDDDDDDDD";
//
//		$add = new Address();
//		$add->street = "street 1";
//
//		$org = new Org();
//		$org->ogrn = "OGRN";
//
//		$noEn = new NoEntity();
//		$noEn->noName = "asdfsdfsdf";
//		$noEn->param = 123	;
//		$add->noEntity = $noEn;
//
//		$add->organizations[] = $org;
//		$add->organizations[] = $org;
//
//		$add->ooo = $org;
//		$lolo->arr = $add;

//
//		$lm = new LoloManager();
//////
//		$stored = $lm->findById("61ee730342bcd0159b172672");
//		print_r($stored);
//		$lm->save($lolo);

//	}

//	public function lolotestSave()
//	{
////		$ent = new Example();
////		$ent->name = "some name";
////		$ent->age = 100;
////
////
////		$addr = new Address();
////		$addr->building = "45";
////		$addr->street = "Lenina";
////
////
////		for ($i = 0; $i < 2; $i++)
////		{
////			$org = new Org();
////			$org->inn = "INN_{$i}";
////			$org->ogrn = "OGRN_{$i}";
////			$addr->organizations[] = $org;
////		}
////
////		$ent->address = $addr;
////		$saved = $this->manager->save($ent);
//
////		print_r($saved);
//		$stored = $this->manager->findById("61eac41203846e364617d832");
//		print_r($stored);
//
////		$this->assertInstanceOf(Address::class, $stored->address);
//		$this->assertInstanceOf(Org::class, $stored->address->organizations[0]);
//
////		$this->assertNotNull($saved);
////		$this->assertInstanceOf(Example::class, $saved);
////
////		$this->assertNotNull($saved->address);
////		$this->assertInstanceOf(Address::class, $saved->address);
////
////		$this->assertEquals("45", $saved->address->building);
////		$this->assertCount(2, $saved->address->organizations);
////		$this->assertEquals("INN_0", $saved->address->organizations[0]->inn);
//	}

//	public function testGetById()
//	{
//
//		$saved = $this->manager->findById("61eaa60bf4646700cb12ab92");
//
//
//		print_r($saved);
//		$this->assertNotNull($saved);
//		$this->assertInstanceOf(Example::class, $saved);
//	}


//	public function testFind()
//	{
//		$ent = new Example();
//		$ent->name = "find_by_name";
//		$ent->age = 100;
//		$saved = $this->manager->save($ent);
//
//		$this->assertNotNull($saved);
//		$this->assertInstanceOf(Example::class, $saved);
//		$saved = $this->manager->find(["name" => "find_by_name"]);


//		print_r($saved->getValues());
//		$saved = $this->manager->findOne([]);
//		$saved->
//		var_dump($saved->current());
//		var_dump(iterator_to_array($saved));
//		/** @var $item ExampleEntity */
//		foreach ($saved as $item)
//		{
////			var_dump($item->);
//			$item->kuku();
//		}
//	}

//
//	public function testUpdateViaSave()
//	{
//		$ent = new ODMTest();
//		$ent->name = "some name";
//		$ent->age = 300;
//		$saved = $this->manager->save($ent);
//
//		$saved->age = 500;
//		$savedAgain = $this->manager->save($ent);
//
//		$this->assertEquals(500, $savedAgain->age);
//		$this->assertEquals($saved->id, $savedAgain->id);
//	}


//	--------------------------

//
//	public function testGetConnection()
//	{
//		$client = $this->manager->getClient();
//		$this->assertTrue($client instanceof Client);
//	}
//
//	public function testGetEntityClassName()
//	{
//		$this->assertEquals('ODMTests\Entity\ODMTest', $this->manager->getEntityClassName());
//	}
//
//
//	public function testSave()
//	{
//		$ent = new ODMTest();
//		$ent->name = "some name";
//		$ent->age = 100;
//		$saved = $this->manager->save($ent);
//
//		$this->assertNotNull($saved);
//		$this->assertInstanceOf('ODMTests\Entity\ODMTest', $saved);
//	}
//
//	public function testObjectId()
//	{
//		$strId = "5cb6cf1440f72c0001746242";
//		$id = new ObjectId($strId);
//
//		$this->assertEquals($strId, $id->__toString());
//		$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
//	}
//
//
//	public function testUpdateViaSave()
//	{
//		$ent = new ODMTest();
//		$ent->name = "some name";
//		$ent->age = 300;
//		$saved = $this->manager->save($ent);
//
//		$saved->age = 500;
//		$savedAgain = $this->manager->save($ent);
//
//		$this->assertEquals(500, $savedAgain->age);
//		$this->assertEquals($saved->id, $savedAgain->id);
//	}
//
//
//	public function testFindById()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//
//		$address = new ODMAddress();
//		$address->building = 45;
//		$address->street = "Lenina street";
//
//		$ent->address = $address;
//
//		for ($i = 1; $i <= 2; $i++)
//		{
//			$user = new ODMUser();
//			$user->name = "name{$i}";
//			$user->password = "password";
//			$ent->users[] = $user;
//		}
//
//		$org = new ODMOrg();
//		$org->inn = "12151540043";
//		$org->ogrn = "123435451423654634";
//		$ent->address->organizations[] = $org;
//
//		$saved = $this->manager->save($ent);
//
//		$res = $this->manager->findById($saved->id);
//
//		$this->assertNotNull($res);
//		$this->assertEquals(1000, $res->age);
//
//		$this->assertCount(2, $res->users);
//		$this->assertEquals(45, $res->address->building);
//
//		$this->assertCount(1, $res->address->organizations);
//		$this->assertEquals("12151540043", $res->address->organizations[0]->inn);
//	}
//
//	public function testFindByIdNotFound()
//	{
//		$strId = "5cb6cf1440f72c0001746242";
//		$saved = $this->manager->findById($strId);
//		$this->assertNull($saved);
//	}
//
//
//	public function testFindByIdWithNull()
//	{
//		$strId = null;
//		$saved = $this->manager->findById($strId);
//		$this->assertNull($saved);
//	}
//
//	public function testRemoveById()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//		$saved = $this->manager->save($ent);
//
//		$res = $this->manager->removeById($saved->id);
//
//		$find = $this->manager->findById($saved->id);
//		$this->assertNull($find);
//	}
//
//	public function testRemoveByCondition()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//		$saved = $this->manager->save($ent);
//
//		$ent = new ODMTest();
//		$ent->name = "test name 2";
//		$ent->age = 1000;
//		$saved = $this->manager->save($ent);
//
//		$res = $this->manager->remove(["age" => 1000]);
//
//		$this->assertEquals(2, $res->getDeletedCount());
//		$this->assertEquals(0, count($this->manager->getCollection()->find()->toArray()));
//	}
//
//
//	public function testUpdateById()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//		$saved = $this->manager->save($ent);
//
//		$res = $this->manager->updateById($saved->id, ['$set' => ["age" => 10000]]);
//
//		$this->assertEquals(1, $res->getModifiedCount());
//
//		$stored = $this->manager->findById($saved->id);
//		$this->assertEquals(10000, $stored->age);
//	}
//
//	public function testUpdate()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//		$saved = $this->manager->save($ent);
//
//		$condition = ["age" => 1000];
//		$update = [
//			'$inc' => ["age" => 10],
//			'$set' => ["name" => "another name"]
//		];
//		$this->manager->update($condition, $update);
//
//		$upd = $this->manager->findById($saved->id);
//
//		$this->assertEquals(1010, $upd->age);
//		$this->assertEquals("another name", $upd->name);
//	}
//
//	public function testFind()
//	{
//		for ($i = 0; $i < 5; $i++)
//		{
//			$ent = new ODMTest();
//			$ent->name = "test name {$i}";
//			$ent->age = 1000 + $i;
//			$saved = $this->manager->save($ent);
//		}
//
//		$res = $this->manager->find(["age" => ['$gte' => 1001]]);
//
//		$c = 0;
//		foreach ($res as $item)
//		{
//			$c++;
//			$this->assertEquals(1000 + $c, $item->age);
//		}
//	}
//
//	public function testCount()
//	{
//		for ($i = 0; $i < 5; $i++)
//		{
//			$ent = new ODMTest();
//			$ent->name = "test name {$i}";
//			$ent->age = 1000 + $i;
//			$this->manager->save($ent);
//		}
//
//		$res = $this->manager->count(["age" => ['$gte' => 1001]]);
//
//		$this->assertEquals(4, $res);
//
//	}
//
//	public function testBuildObjectIdList()
//	{
//		$ids = [
//			"5cc2dc2c7258ab0001585b02",
//			"5cc2dc2c7258ab0001585b03",
//			"5cc2dc2c7258ab0001585b04",
//			"5cc2dc2c7258ab0001585b05",
//		];
//
//		$res = EntityManager::buildObjectIdList($ids);
//
//		$this->assertIsArray($res);
//
//		foreach ($res as $id)
//		{
//			$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
//		}
//	}
//
//	/**
//	 * @expectedException InvalidArgumentException
//	 */
//	public function testBuildObjectIdList_fail()
//	{
//		$ids = [
//			"wrong5cc2dc2c7258ab0001585b",
//			"wrong5cc2dc2c7258ab000158503",
//			"wrong5cc2dc2c7258ab1585b04",
//			"wrong5cc2dc2c7250001585b05",
//		];
//
//		$res = EntityManager::buildObjectIdList($ids);
//
//		$this->assertIsArray($res);
//
//		foreach ($res as $id)
//		{
//			$this->assertInstanceOf('MongoDB\BSON\ObjectId', $id);
//		}
//	}
//
//	public function testFetchField()
//	{
//		$ent = new ODMTest();
//		$ent->name = "test name";
//		$ent->age = 1000;
//		$this->manager->save($ent);
//
//		$res = $this->manager->fetchField(["age" => 1000], "name");
//
//		$this->assertEquals("test name", $res);
//
//		$res = $this->manager->fetchField(["age" => 1000], "non_exists_field");
//
//		$this->assertNull($res);
//	}
//
//	public function testFetchColumn()
//	{
//		for ($i = 0; $i < 5; $i++)
//		{
//			$ent = new ODMTest();
//			$ent->name = "test name {$i}";
//			$ent->age = 1000 + $i;
//			$this->manager->save($ent);
//		}
//
//		$res = $this->manager->fetchColumn([], "age");
//
//		$c = 0;
//		foreach ($res as $item)
//		{
//			$this->assertEquals(1000 + $c, $item);
//			$c++;
//		}
//	}

}
