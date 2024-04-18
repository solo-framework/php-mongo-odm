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

use RuntimeLLC\Mongo\EntityManager;

class FinManager extends BaseTestManager
{

	public function getEntityClassName(): string
	{
		return FinEntity::class;
	}
}

