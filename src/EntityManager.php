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

use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\DeleteResult;
use MongoDB\Operation\FindOneAndUpdate;
use MongoDB\UpdateResult;
use RuntimeException;

abstract class EntityManager
{
	protected array $typeMap;
	protected string $collectionName;

	/**
	 * @var Collection
	 */
	protected Collection $collection;

	/**
	 * Name of PHP class that describes an entity for this manager
	 *
	 * @var string
	 */
	protected string $entityClassName;

//	protected $typeMap = [
//		'array' => 'array', //'MongoDB\Model\BSONArray',
//		'document' => 'array',//'MongoDB\Model\BSONArray',//'MongoDB\Model\BSONDocument',
//		'root' => null//'MongoDB\Model\BSONDocument',
//	];


	/**
	 * Options for creating collection
	 *
	 * @return array
	 */
	public function getCollectionOptions(): array
	{
		$opts["typeMap"] = $this->typeMap;
		return $opts;
	}


	public function __construct($collectionOptions = [])
	{
		$this->entityClassName = $this->getEntityClassName();
		if (!$this->entityClassName)
			throw new RuntimeException("You should define entity class name in getEntityClassName() for " . get_called_class());

		/** @var $inst Entity */
		$inst = new $this->entityClassName;
		//$this->typeMap["root"] = $this->entityClassName;
		$this->typeMap = $inst->getTypeMap();
		$this->collectionName = $inst->getCollectionName();

		if ($this->collectionName)
		{
			$opts = $this->getCollectionOptions() + $collectionOptions;
			$this->collection = $this->getClient()->getDatabase()->selectCollection($this->collectionName, $opts);
		}
	}

	/**
	 * Returns collection object
	 *
	 * @return Collection
	 */
	public function getCollection(): Collection
	{
		return $this->collection;
	}

	/**
	 * Returns client
	 *
	 * @return Client
	 */
	abstract public function getClient() : Client;

	public abstract function getEntityClassName(): string;

	/**
	 * Gets entity by Id
	 * @see FindOne::__construct() for supported options
	 *
	 * @param $entityId
	 *
	 * @return Entity|null
	 */
	public function findById($entityId) : Entity|null
	{
		$entityId = new ObjectId($entityId);

		$opts["typeMap"] = $this->typeMap;
		$res = $this->collection->findOne(["_id" => $entityId], $opts);

		if ($res == null)
			return null;

		$mapper = new Mapper($res, $this->entityClassName);
		return $mapper->convert();
	}

	/**
	 * Saves an entity
	 *
	 * @param Entity $entity
	 * @param array $options
	 *
	 * @return Entity
	 * @see UpdateOne::__construct() for supported options
	 *
	 */
	public function save(Entity $entity, array $options = []): Entity
	{
		$ignored = $entity->getIngoredFields();
		$doc = (array)$entity;

		foreach ($ignored as $name => $val)
		{
			if ($val === false)
				unset($doc[$name]);
		}

		if (!isset($doc["id"]))
		{
			unset($doc["id"]);
			$res = $this->collection->insertOne($doc, $options);
			$entity->id = $res->getInsertedId();
		}
		else
		{
			$id = new ObjectId($doc["id"]);
			unset($doc["id"]);
			$this->collection->updateOne(["_id" => $id], ['$set' => $doc], $options);
		}

		return $entity;
	}

	/**
	 * Removes an entity by id
	 *
	 * @param $entityId
	 * @param array $options
	 *
	 * @return DeleteResult
	 * @see DeleteOne::__construct() for supported options
	 *
	 */
	public function removeById($entityId, array $options = []): DeleteResult
	{
		return $this->collection->deleteOne(["_id" => new ObjectId($entityId)], $options);
	}

	/**
	 * Removes an entity by condition
	 *
	 * @param $condition
	 * @param array $options
	 *
	 * @return DeleteResult
	 */
	public function remove($condition, array $options = []): DeleteResult
	{
		return $this->collection->deleteMany($condition, $options);
	}

	/**
	 * Updates an entity by id
	 *
	 * @see UpdateOne::__construct() for supported options
	 *
	 * @param $entityId
	 * @param $update
	 * @param array $options
	 *
	 * @return UpdateResult
	 */
	public function updateById($entityId, $update, array $options = []): UpdateResult
	{
		return $this->collection->updateOne(
			["_id" => new ObjectId($entityId)],
			$update,
			$options
		);
	}

	/**
	 * Updates an entities by condition
	 *
	 * @see UpdateMany::__construct() for supported options
	 *
	 * @param $condition
	 * @param $update
	 * @param array $options
	 *
	 * @return UpdateResult
	 */
	public function update($condition, $update, array $options = []): UpdateResult
	{
		return $this->collection->updateMany($condition, $update, $options);
	}

	/**
	 * To find entities by condition
	 *
	 * @see Find::__construct() for supported options
	 *
	 * @param $filter
	 * @param array $options
	 *
	 * @return DataSet
	 */
	public function find($filter, array $options = []) : DataSet
	{
		$options["typeMap"] = $this->typeMap;
		$cursor = $this->collection->find($filter, $options);
		return new DataSet($cursor);
	}

	/**
	 * Returns one entity by condition
	 *
	 * @param $filter
	 * @param array $options
	 *
	 * @return Entity
	 */
	public function findOne($filter, array $options = []): Entity
	{
		$options["typeMap"] = $this->typeMap;
		$res = $this->collection->findOne($filter, $options);
		$mapper = new Mapper($res, $this->entityClassName);
		return $mapper->convert();
	}

	/**
	 * Counts entities by condition
	 *
	 * @param array $filter
	 * @param array $options
	 *
	 * @return int
	 *@see CountDocuments::__construct() for supported options
	 *
	 */
	public function count(array $filter = [], array $options = []): int
	{
		return $this->collection->countDocuments($filter, $options);
	}

	/**
	 * Создает по списку строковых идентификаторов список ObjectId
	 *
	 * @param array $ids Список идентификатров
	 *
	 * @return array
	 */
	public static function buildObjectIdList(array $ids) : array
	{
		$func = function($val) {
			return new ObjectId($val);
		};

		return array_map($func, $ids);
	}

	/**
	 * Returns only one field value
	 *
	 * @param $condition
	 * @param $name
	 * @param array $options
	 *
	 * @return mixed
	 * @see FindOne::__construct() for supported options
	 *
	 */
	public function fetchField($condition, $name, array $options = []): mixed
	{
		$options["projection"][$name] = true;
		$options["projection"]["_id"] = $name == "_id";
		$options["typeMap"] = [
			'array'    => 'array',
			'document' => 'array',
			'root'     => 'array'
		];

		$res = $this->collection->findOne($condition, $options);

		if (!$res || count($res) == 0)
			return null;

		return $res[$name];
	}

	/**
	 * Returns values of documetns field (like a column in RDBS)
	 *
	 * @param $condition
	 * @param $name
	 * @param array $options
	 *
	 * @return array
	 * @see Find::__construct() for supported options
	 */
	public function fetchColumn($condition, $name, array $options = []): array
	{
		$options["projection"][$name] = true;
		$options["projection"]["_id"] = false;
		$options["typeMap"] = [
			'array'    => 'array',
			'document' => 'array',
			'root'     => 'array'
		];


		$cursor = $this->collection->find($condition, $options);

		$res = [];
		foreach ($cursor as $item)
		{
			$res[] = $item[$name];
		}

		return $res;
	}

	/**
	 * Returns entity with defined fields only
	 * Возвращает сущность с заданным набором полей
	 *
	 * @param array $condition Condition for search
	 * @param array $returnFields List of field names to return
	 * @param array $options
	 *
	 * @return Entity|null
	 */
	public function fetchPartEntity(array $condition, array $returnFields, array $options = []): ?Entity
	{
		$fields = [];
		foreach ($returnFields as $returnField)
			$fields[$returnField] = 1;

		$options["projection"] = $fields;

		$doc = $this->collection->findOne($condition, $options);
		if (!$doc)
			return null;

		$mapper = new Mapper($doc, $this->entityClassName);
		return $mapper->convert();
	}

	/**
	 * Модифицирует и возвращает Один документ.
	 * По-умолчанию, НЕ добавляет новый документ, если ничего не найдено по условию $condition
	 *
	 * @param array $filter
	 * @param array $update Update params
	 * @param array $options Command options
	 *
	 * @return Entity|null
	 */
	public function findOneAndUpdate(array $filter, array $update, array $options = array()): ?Entity
	{
		$opts = [
			"upsert" => false,
			"returnDocument" => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
		] + $options;

		$res = $this->collection->findOneAndUpdate($filter, $update, $opts);
		if ($res == null)
			return null;

		$mapper = new Mapper($res, $this->entityClassName);
		return $mapper->convert();
	}

	/**
	 * Constructs a findAndModify command for replacing a document.
	 *
	 * @param array $filter
	 * @param array $update
	 * @param array $options
	 *
	 * @return Entity|null
	 */
	public function findOneAndReplace(array $filter, array $update, array $options = array()): ?Entity
	{
		$opts = [
				"upsert" => false,
				"returnDocument" => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
			] + $options;

		$res = $this->collection->findOneAndReplace($filter, $update, $opts);
		if ($res == null)
			return null;

		$mapper = new Mapper($res, $this->entityClassName);
		return $mapper->convert();
	}
}

