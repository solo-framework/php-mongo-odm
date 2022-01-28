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
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use RuntimeLLC\Mongo\Entity;

class PersonEntity extends Entity
{
	public int $age = 0;

	public string|null $name = null;

	public string|null $password = null;

	public ?UTCDateTime $date = null;

	public ?array $ignoreField = [];

	/**
	 * @var array<int>
	 */
	public array $ints = [];

	/**
	 * Returns name of collection for store this type
	 *
	 * @return string
	 */
	public function getCollectionName() : string
	{
		return "odm_person";
	}

	public function getEntityRelations(): array
	{
		return [];
	}

	public function getIngoredFields(): array
	{
		return [
			"ignoreField" => false
		];
	}
}

