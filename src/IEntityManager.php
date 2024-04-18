<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace RuntimeLLC\Mongo;

interface IEntityManager
{
	/**
	 * Returns entity class name
	 *
	 * @example return User::class
	 * @return string
	 */
	public function getEntityClassName(): string;
}

