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

/**
 * Class ODMTestManager
 * @package ODMTests
 *
 * method Example findById($entityId)
 * method Example save(Example $entity, $options = [])
 */
class ExampleManager extends BaseTestManager
{

	/**
	 * Gets full name of entity class name
	 *
	 * @return string
	 */
	public function getEntityClassName(): string
	{
		return ExampleEntity::class;
	}
}

