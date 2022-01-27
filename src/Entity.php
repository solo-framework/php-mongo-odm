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

use JetBrains\PhpStorm\ArrayShape;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;

abstract class Entity implements Unserializable, Serializable
//abstract class Entity implements Persistable//Unserializable, Serializable
{
	/**
	 * Описывает список сущностей
	 */
	const TYPE_ARRAY_ENTITIES = "TYPE_ARRAY_ENTITIES";

	/**
	 * Описывает одну сущность
	 */
	const TYPE_ENTITY = "TYPE_ENTITY";

	/**
	 * Это не сущность, а объект любого класса (структура)
	 */
	const TYPE_NOENTITY = "TYPE_NOENTITY";

	/**
	 * Идентификатор сущности
	 *
	 * @var ObjectId|null
	 */
	public ObjectId|null $id = null;


	/**
	 * Возвращает TypeMap
	 *
	 * @return array
	 */
	public function getTypeMap(): array
	{
		return [
			"root"     => $this::class,
			"document" => "array",
			"array"    => "array"
		];
	}

	/**
	 * Возвращает метаинформацию по связям полей с другими сущностями,
	 *
	 * @return array
	 */
	public abstract function getEntityRelations() : array;


	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string|null
	 */
	public abstract function getCollectionName(): ?string;

	/**
	 *
	 *
	 * @return array
	 */
	public abstract function getIngoredFields(): array;

	/**
	 * Constructs the object from a BSON array or document
	 * Called during unserialization of the object from BSON.
	 * The properties of the BSON array or document will be passed to the method as an array.
	 * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
	 *
	 * @param array $data Properties within the BSON array or document.
	 */
	public function bsonUnserialize(array $data)
	{
		$ignored = $this->getIngoredFields();
//		print_r($ignored);
//
		foreach ($ignored as $name => $val)
		{
			if ($val === false)
			{
//				print_r("\n DELETE {$name}\n");
//				print_r($data);
				unset($data[$name]);
			}
		}

		foreach ($data as $k => $value)
		{
			if ($k == "_id")
				continue;
			$this->$k = $value;
		}

		if (array_key_exists("_id", $data))
			$this->id = $data["_id"];
	}

	public function bsonSerialize(): array
	{
		print_r("SERIALIZE\n");
		throw new \Exception("bsonSerialize");
		return (array)$this;
	}
}