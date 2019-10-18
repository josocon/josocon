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

use josocon\DB;


require_once __DIR__ . '/_includes/template.php';

$path = \rawurldecode (\substr (\explode ('?', $_SERVER['REQUEST_URI'])[0], 1));

try {
\ob_start ();
$db = new DB (DB_PATH);

if ('' === $path) {
$events = $db->getEvents ();
print_header ('/', 'じょそこん', '');
?>
<nav class='items'>
<ul>
<?php
if (isset ($_SESSION['user'])) {
?><li><a href='/new/'>新規作成</a></li><?php
}

foreach ($events as $event) {
	\printf ("<li><a href='/%s'>%s</a></li>", escape ($event->name), escape ($event->title));
}
?>
</ul>
</nav>
<!--
<p>
<ruby>女装子<rp>（</rp><rt>じょそこ</rt><rp>）</rp></ruby>があふれる東大の学園祭…
その中で一番の女装子は誰なのか――
</p>
<p>
今年も、東京大学第69回駒場祭にて東大女装子コンテストを開催いたします！ 女装子たちによるパフォーマンスステージをお楽しみください。 
</p>
<h3>じょそこんステージ</h3>
<p>
場所　いちょうステージ
</p>
<p>
日時　11/24 10:45 - 12:05
</p>
<h3>女装・メイド喫茶</h3>
<p>
場所　7号館724教室
</p>
<p>
日時　11/24 (13:00 -), 11/25, 11/26: (終日)
</p>
-->
<p>
<span class='square'>
<iframe src='https://twitter.menherausercontent.org/tweets.xhtml#ut_josocon'/>
</span>
</p>
<?php
print_footer ();

} else {
	$event = $db->getEventByName ($path);
	if (!$event) {
		http_status (404);
		print_header ('/' . $path, 'ページが見つかりませんでした', '');

		print_footer ();
	} else {
		print_header ('/' . $path, $event->title, '');
		if (isset ($_SESSION['user'])) {
			\printf ("<menu class='page-menu'><ul><li><a href='%s'>ページを編集</a></li></ul></menu>", escape ('/edit?name=' . \urlencode ($event->name)));
		}
		\printf ('<josocon-markdown>%s</josocon-markdown>', escape ($event->description));
		$subevents = $db->getSubevents ($event);
		foreach ($subevents as $subevent) {
			echo '<section>';
			echo '<h2>', escape ($subevent->title), '</h2>';
			\printf ('<josocon-markdown>%s</josocon-markdown>', escape ($subevent->description));
			echo '</section>';
		}
		
		echo '<section>';
		echo '<h2>項目一覧</h2>';
		$items = $db->getItems ($event);
		foreach ($items as $item) {
			echo '<section>';
			echo '<h3>', escape ($item->name), '</h3>';
			\printf ('<josocon-markdown>%s</josocon-markdown>', escape ($item->description));
			echo '</section>';
		}
		echo '</section>';
		print_footer ();
	}
}

\ob_flush ();
} catch (\Throwable $e) {
	\ob_clean ();
	http_status (500);
	print_header ('/' . $path, 'エラー', '');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}

