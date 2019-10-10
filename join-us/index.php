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


require_once __DIR__ . '/../_includes/template.php';

print_header ('/join-us/', 'スタッフ募集');
?>
<josocon-markdown><![CDATA[
東大女装子コンテスト実行委員会はスタッフをいつでも募集中です。女装に興味がある方大歓迎です！
駒場祭でのステージパフォーマンスに向けて企画、広報、運営などの業務を行います。

スタッフ希望の方はut.josocon @ gmail.com あるいは東大女装子コンテスト公式Twitterアカウント [@ut_josocon](https://twitter.com/ut_josocon) までご連絡ください。
]]></josocon-markdown>
<?php
print_footer ();

