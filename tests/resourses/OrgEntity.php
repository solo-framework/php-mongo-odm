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

class OrgEntity extends Entity
{
	public string|null $inn = null;

	public string|null $ogrn = null;

	public ?AddressEntity $address = null;

	public function getCollectionName(): ?string
	{
		return null;
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
	}
}

