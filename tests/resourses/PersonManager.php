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

use RuntimeLLC\Mongo\DataSet;
use RuntimeLLC\Mongo\Entity;

/**
 * @method PersonEntity save(Entity $entity, array $options = [])
 * @method DataSet find($filter, array $options = [])
 */
class PersonManager extends BaseTestManager
{

	public function getEntityClassName(): string
	{
		return PersonEntity::class;
	}
}

