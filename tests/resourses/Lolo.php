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
use RuntimeLLC\Mongo\Entity;

class Lolo extends Entity
{
	public $inn = null;

	public $ogrn = null;

	public $arr = null;

	public function getCollectionName(): ?string
	{
		return "lolo";
	}

//	public function getTypeMap(): array
//	{
//		$typeMap = parent::getTypeMap();
//		$typeMap["fieldPaths"] = [
//			"arr" => Address::class
//		];
//		return $typeMap;
//	}

	#[ArrayShape(["arr" => "array"])]
	/**
	 *
	 */
	public function getEntityRelations(): array
	{
		return [
			"arr" => ["type" => self::TYPE_ENTITY, "class" => Address::class]
		];
	}
}


