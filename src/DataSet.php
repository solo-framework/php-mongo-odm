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

	public function __construct(Cursor $cursor)
	{
		$this->cursor = $cursor;
	}

	public function getCursor(): Cursor
	{
		return $this->cursor;
	}

	public function current()
	{
		return $this->cursor->current();
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

