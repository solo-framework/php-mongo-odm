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

class RootManager extends BaseTestManager
{

	public function getEntityClassName(): string
	{
		return RootEntity::class;
	}
}

