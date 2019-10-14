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


class Set extends Collection
{
	private $items = [];
	
	// @override
	public function getIterator (): \Traversable
	{
		\sort ($this->items);
		
		return new \ArrayIterator ($this->items);
	}
	
	// @override
	public function count (): int
	{
		return \count ($this->items);
	}
	
	public function add ($item): void
	{
		if (\in_array ($item, $this->items, true)) return;
		$this->items[] = $item;
	}
	
	public function remove ($item): void
	{
		$key = \array_search ($item, $this->items, true);
		if (false === $key) return;
		unset ($this->items[$key]);
	}
	
	public function has ($item): bool
	{
		return \in_array ($item, $this->items, true);
	}
}
