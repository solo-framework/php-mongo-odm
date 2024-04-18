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

class RootEntity extends Entity
{
	public string|null $name = null;

	public int|null $year = null;

	/**
	 * @var OrgEntity[]
	 */
	public array|null $orgs = null;

	public function getCollectionName(): ?string
	{
		return "root_collection";
	}

	public function getEntityRelations(): array
	{
		return [
			"orgs" => ["type" => self::TYPE_ARRAY_ENTITIES, "class" => OrgEntity::class]
		];
	}

	public function getIngoredFields(): array
	{
		return [];
	}
}


