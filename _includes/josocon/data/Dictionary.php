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


class Dictionary extends Collection implements \ArrayAccess
{
	private $items = [];
	
	public function getIterator (): \Traversable
	{
		return new \ArrayIterator ($this->items);
	}
	
	protected function testKey (string $key = null): string
	{
		if (null === $key) {
			throw new \TypeError ('key is null');
		}
		
		if ('' === $key) {
			throw new \TypeError ('key is empty');
		}
		
		if (\is_numeric ($key)) {
			throw new \TypeError ('numeric key not allowed');
		}
		return $key;
	}
	
	public function offsetSet ($key, $value): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('dictionary is frozen');
		}
		$key = $this->testKey ($key);
		list ($value) = $this->testType ($value);
		$this->items[$key] = $value;
	}
	
	public function offsetGet ($key) // : mixed
	{
		$key = $this->testKey ($key);
		return $this->items[$key] ?? null;
	}
	
	public function offsetExists ($key): bool
	{
		return isset ($this->items[$key]);
	}
	
	public function offsetUnset ($key): void
	{
		if ($this->isFrozen ()) {
			throw new \TypeError ('dictionary is frozen');
		}
		$key = $this->testKey ($key);
		unset ($this->items[$key]);
	}
	
	public function __get (string $key) // : mixed
	{
		return $this[$key];
	}
	
	public function __set (string $key, $value): void
	{
		$this[$key] = $value;
	}
	
	public function __isset (string $key): bool
	{
		return isset ($this[$key]);
	}
	
	public function __unset (string $key): void
	{
		unset ($this[$key]);
	}
	
	public function count (): int
	{
		return \count ($this->items);
	}
}
