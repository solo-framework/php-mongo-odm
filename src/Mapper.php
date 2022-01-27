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

class Mapper
{
	private mixed $doc;
	private string $className;

	public function __construct(mixed $doc, string $className)
	{
		$this->doc = $doc;
		$this->className = $className;
	}

	/**
	 * Выполнить мапинг
	 *
	 * @return Entity
	 */
	public function convert() : Entity
	{
//		if (is_a($this->doc, Entity::class))
//			return $this->doc;

//		print_r("\n!!!!!!!!!!!!!!!!!!!!!!!!!!\n");
//		print_r($this->doc);
//		print_r("seee\n");
//		var_dump(is_a($this->doc, Entity::class));
//		print_r(get_class($this->doc));
//		print_r("\n!!!!!!!!!!!!!!!!!!!!!!!!!!\n");

		if (!is_a($this->doc, Entity::class))
		{
			$id = $this->doc["_id"];
			unset($this->doc["_id"]);
		}



		$obj = self::arrayToObjectRecurively($this->doc, ["type" => Entity::TYPE_ENTITY, "class" => $this->className]);
//		$obj->id = $id;

		return $obj;
	}

	/**
	 * Рекурсивно преобразовыет массив в сущность
	 *
	 * @param mixed $data Данные
	 * @param array $options Опции
	 *
	 * @return array|null
	 */
	private static function arrayToObjectRecurively(mixed $data, array $options): mixed
	{
		if ($options["type"] == Entity::TYPE_ENTITY || $options["type"] == Entity::TYPE_NOENTITY)
		{
			$object = new $options["class"];
			$fieldMeta = [];
			if ($options["type"] == Entity::TYPE_ENTITY)
				$fieldMeta = $object->getEntityRelations();

			foreach ($data as $name => $value)
			{
				if (property_exists($object, $name))
				{
					if (is_array($value) && isset($fieldMeta[$name]))
					{
						$object->$name = self::arrayToObjectRecurively($value, $fieldMeta[$name]);
					}
					else
					{
						$object->$name = $value;
					}
				}
			}
			return $object;
		}
		else if ($options["type"] == Entity::TYPE_ARRAY_ENTITIES)
		{
			$list = array();
			foreach ($data as $key => $value)
			{
				$list[$key] = self::arrayToObjectRecurively(
					$value,
					["type" => Entity::TYPE_ENTITY, "class" => $options["class"]]
				);
			}
			return $list;
		}

		return null;
	}
}

