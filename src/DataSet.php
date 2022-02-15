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

use MongoDB\Driver\Cursor;

class DataSet implements \Iterator
{
	protected Cursor $cursor;
	protected ?string $entityClassName;

	public function __construct(Cursor $cursor, $entityClassName = null)
	{
		$this->cursor = $cursor;
		$this->entityClassName = $entityClassName;
	}

	public function getCursor(): Cursor
	{
		return $this->cursor;
	}

	public function current()
	{
		$doc = $this->cursor->current();
		if (is_null($doc))
		{
			return null;
		}

		if ($this->entityClassName)
		{
			$mapper = new Mapper($doc, $this->entityClassName);
			return $mapper->convert();
		}
		else
		{
			return $doc;
		}
	}

	public function next()
	{
		$this->cursor->next();
	}

	public function key()
	{
		return $this->cursor->key();
	}

	public function valid(): bool
	{
		return $this->cursor->valid();
	}

	public function rewind()
	{
		$this->cursor->rewind();
	}

	public function getValues(): array
	{
		return iterator_to_array($this, false);
	}
}

