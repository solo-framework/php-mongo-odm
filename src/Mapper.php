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
	 * @return Entity|null
	 */
	public function convert() : ?Entity
	{
		if ($this->doc == null)
			return null;

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
	 * @return mixed
	 */
	private static function arrayToObjectRecurively(mixed $data, array $options): mixed
	{
		if ($options["type"] == Entity::TYPE_ENTITY || $options["type"] == Entity::TYPE_NOENTITY)
		{
			$object = new $options["class"];
			$relations = [];
			if ($options["type"] == Entity::TYPE_ENTITY)
				$relations = $object->getEntityRelations();

			foreach ($data as $name => $value)
			{
				if (property_exists($object, $name))
				{
					if (is_array($value) && isset($relations[$name]))
					{
						$object->$name = self::arrayToObjectRecurively($value, $relations[$name]);
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

