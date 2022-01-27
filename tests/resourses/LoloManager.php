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

class LoloManager extends BaseTestManager
{

	public function getEntityClassName(): string
	{
		return Lolo::class;
	}
}

