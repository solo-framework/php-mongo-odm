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

use JetBrains\PhpStorm\ArrayShape;
use RuntimeLLC\Mongo\Entity;

class ExampleEntity extends Entity
{
	public string|null $name = null;

	public int $age = 0;

	public AddressEntity $address;


	/**
	 * @var PersonEntity[]|null
	 */
	public array|null $persons = null;

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string
	 */
	public function getCollectionName(): string
	{
		return "odm_example";
	}

	public function getEntityRelations(): array
	{
		return [
			"address" => ["type" => self::TYPE_ENTITY, "class" => AddressEntity::class]
		];
	}

	public function getIngoredFields(): array
	{
		return [];
		// TODO: Implement getIngoredFields() method.
	}
}

