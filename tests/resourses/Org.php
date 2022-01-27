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

class Org extends Entity
{
	public $inn = null;

	public $ogrn = null;

	public function getCollectionName(): ?string
	{
		return null;
	}

	public function getEntityRelations(): array
	{
		return [];
	}
}

