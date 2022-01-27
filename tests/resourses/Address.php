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

use RuntimeLLC\Mongo\Entity;

class Address extends Entity
{
	public string|null $street = null;

	public string|null $building = null;

	public $ooo = null;

	public $noEntity = null;

	/**
	 * @var Org[]
	 */
	public array $organizations = [];

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public function getCollectionName() : ?string
	{
		// Если NULL - не храним в отдельной коллекции, т.е. это вложенная сущность
		return null;
	}

	public function getEntityRelations(): array
	{
		return [
			"ooo" => ["type" => self::TYPE_ENTITY, "class" => Org::class],
			"organizations" => ["type" => self::TYPE_ARRAY_ENTITIES, "class" => Org::class],
			"noEntity" => ["type" => self::TYPE_NOENTITY, "class" => NoEntity::class],
		];
	}

//	public function getTypeMap(): array
//	{
//		$typeMap = parent::getTypeMap();
//		$typeMap["fieldPaths"] = [
////			"organizations.$" => Org::class,
//			"ooo" => Org::class
////			"address" => Address::class,
////			"address.organizations.$" => Org::class
//		];
//		return $typeMap;
//	}

//	public function getTypeMap(): array
//	{
//		$typeMap = parent::getTypeMap();
//		$typeMap["fieldPaths"] = [
//			"organizations.0" => Org::class
////			"organizations.$" => ["$" => Org::class]
//		];
//		return $typeMap;
//	}
}

