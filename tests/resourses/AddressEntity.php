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

class AddressEntity extends Entity
{
	public string|null $street = null;

	public string|null $building = null;

	public ?JustStruct $someStruct = null;

	/**
	 * Возвращает имя коллекции, где хранятся сущности этого типа
	 *
	 * @return string|null
	 */
	public function getCollectionName() : ?string
	{
		// Если NULL - не храним в отдельной коллекции, т.е. это вложенная сущность
		return null;
	}

	public function getEntityRelations(): array
	{
		return [
			"someStruct" => ["type" => self::TYPE_NOENTITY, "class" => JustStruct::class],
		];
	}

	public function getIngoredFields(): array
	{
		return [];
	}
}

