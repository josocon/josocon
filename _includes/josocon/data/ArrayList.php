<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon\data;

/**
	Copyright 2019 (C) 東大女装子コンテスト実行委員会

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	https://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.

	@license Apache-2.0
	@file
*/


class ArrayList extends Collection implements \ArrayAccess
{
	private $items = [];
	
	public static function getInstance (string $type, ... $objects): ArrayList
	{
		return new class ($type, ... $objects) extends ArrayList
		{
			public function __construct (string $type, ... $objects)
			{
				$this->setType ($type);
				$this->push (... $objects);
			}
		};
	}
	
	public function toArray (): array
	{
		return $this->items;
	}
	
	public function getIterator (): \Traversable
	{
		return new class ($this) implements \Iterator
		{
			private $list;
			private $index = 0;
			
			public function __construct (ArrayList $list)
			{
				$this->list = $list;
			}
			
			public function current () // : mixed
			{
				return $list[$this->index];
			}
			
			public function key (): int
			{
				return $this->index;
			}
			
			public function next (): void
			{
				$this->index++;
				if (!is_integer ($this->index)) {
					throw new \OverflowException ('index overflow');
				}
			}
			
			public function rewind (): void
			{
				$this->index = 0;
			}
			
			public function valid (): bool
			{
				return $this->list->count () > $this->index
					&& 0 <= $this->index && \is_integer ($this->index);
			}
		};
	}
	
	public function count (): int
	{
		return \count ($this->items);
	}
	
	public function isValidOffset ($i): bool
	{
		if (!\is_integer ($i)) {
			return false;
		}
		
		if (0 > $i || $this->count () <= $i) {
			return false;
		}
		return true;
	}
	
	public function isInsertableOffset ($i): bool
	{
		if (!\is_integer ($i)) {
			return false;
		}
		
		if (0 > $i || $this->count () < $i) {
			return false;
		}
		return true;
	}
	
	public function offsetSet ($i, $value): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if (!$this->isInsertableOffset ($i)) {
			throw new \OutOfRangeException ('invalid offset');
		}
		list ($value) = $this->testType ($value);
		$this->items[$i] = $value;
	}
	
	public function offsetGet ($i) // : mixed
	{
		if (!$this->isValidOffset ($i)) {
			throw new \OutOfRangeException ('invalid offset');
		}
		return $this->items[$i];
	}
	
	public function offsetExists ($i): bool
	{
		return $this->isValidOffset ($i);
	}
	
	public function offsetUnset ($i): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if (!$this->isValidOffset ($i)) {
			throw new \OutOfRangeException ('invalid offset');
		}
		$this->remove ($i);
	}
	
	public function remove (int $i): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if (!$this->isValidOffset ($i)) {
			throw new \OutOfRangeException ('invalid offset');
		}
		unset ($this->items[$i]);
		$this->items = \array_values ($this->items);
	}
	
	public function insert (int $i, ... $items): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if (!$this->isInsertableOffset ($i)) {
			throw new \OutOfRangeException ('invalid offset');
		}
		if (\count ($items) < 1) {
			return;
		}
		\array_splice ($this->values, $i, 0, $items);
	}
	
	public function push (... $items): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		foreach ($items as $item) {
			$this->items[] = $this->testType ($item)[0];
		}
	}
	
	public function pop () // : mixed
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if ($this->count () < 1) {
			throw new \OutOfRangeException ('invalid offset');
		}
		return \array_pop ($this->items);
	}
	
	public function unshift (... $items): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if (\count ($items) < 1) {
			return;
		}
		foreach ($items as &$item) {
			$item = $this->testType ($item)[0];
		}
		\array_unshift ($this->items, ... $items);
	}
	
	public function shift () // : mixed
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		if ($this->count () < 1) {
			throw new \OutOfRangeException ('invalid offset');
		}
		return \array_shift ($this->items);
	}
	
	public function reverse (): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('list is frozen');
		}
		\array_reverse ($this->items);
	}
}
