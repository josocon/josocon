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


abstract class Collection implements \IteratorAggregate, \Countable
{
	private $frozen = false;
	private $types;
	
	public function toArray (): array
	{
		$a = [];
		foreach ($this as $item) {
			$a[] = $item;
		}
		return $a;
	}
	
	public function freeze (): Collection
	{
		$this->frozen = true;
		return $this;
	}
	
	public function isFrozen (): bool
	{
		return $this->frozen;
	}
	
	protected function setType (string ... $types): void
	{
		$this->types = $types;
	}
	
	public function getType (): ?iterable
	{
		return $this->types;
	}
	
	protected function testType (... $items): array
	{
		$types = $this->getType ();
		if (null === $types) {
			return $items;
		}
		
		foreach ($items as $i => &$item) {
			if (!isset ($types[$i])) {
				throw new \TypeError ('invalid type');
			}
			
			$type = $types[$i];
			
			switch ($type) {
				case 'null':
				case 'void':
					$item = null;
					break;
				
				case 'bool':
					$item = (bool) $item;
					break;
				
				case 'int':
					$item = (int) $item;
				case 'float':
					if (!\is_numeric ($item)) {
						throw new \TypeError ('number expected');
					}
					break;
				
				case 'string':
					$item = (string) $item;
					break;
				
				case 'callable':
					if (!\is_callable ($item)) {
						throw new \TypeError ('callable expected');
					}
					break;
				
				case 'iterable':
					if (!\is_iterable ($item)) {
						throw new \TypeError ('iterable expected');
					}
					break;
				
				case 'object':
					if (!\is_object ($item)) {
						throw new \TypeError ('object expected');
					}
					break;
				
				default:
					if ($type !== \get_class ($item)) {
						throw new \TypeError ('item is of invalid type');
					}
			}
		}
		return $items;
	}
	
	public function forEach (callable $callback): void
	{
		foreach ($this as $item) {
			$callback ($item);
		}
	}
	
	public function mapToArray (callable $callback): array
	{
		$a = [];
		foreach ($this as $item) {
			$a[] = $callback ($item);
		}
		return $a;
	}
	
	public abstract function getIterator (): \Traversable;
	
	public abstract function count (): int;
	
	public function isEmpty (): bool
	{
		return 1 > $this->count ();
	}
}
