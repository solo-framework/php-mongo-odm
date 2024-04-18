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

class FinEntity extends Entity
{

	public function getEntityRelations(): array
	{
		return [];
	}

	public function getCollectionName(): ?string
	{
		return "fin";
	}

	public function getIngoredFields(): array
	{
		return [];
	}
}

