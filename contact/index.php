<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon;

/**
	Copyright 2017 (C) 東大女装子コンテスト実行委員会

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

print_header ('/contact/', 'お問い合わせ');
?>
<josocon-markdown><![CDATA[
東大女装子コンテストへのお問い合わせは以下のメールアドレスまでお願いいたします。
ご質問・ご意見など、お気軽にご連絡ください。 

ut.josocon @ gmail.com

サイトの技術的な問題、セキュリティに関する問題などは tech @ josocon.u-tokyo.eu.org または [Twitter: @_uts2](https://twitter.com/_uts2) に直接連絡していただいても構いません。
]]></josocon-markdown>
<?php
print_footer ();

