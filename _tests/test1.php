<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon;

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

use josocon\data\Dictionary;
use josocon\data\StringList;

require_once __DIR__ . '/../_includes/autoload.php';


$d = new Dictionary;
$d->a = 1;
\var_dump ($d, $d['a']);
foreach ($d as $item) {
	\var_dump ($item);
}

$l = new StringList ('foo', 'bar');
\var_dump ($l);

