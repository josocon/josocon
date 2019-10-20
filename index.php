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
	$page = (int) ($_GET['page'] ?? '');
	if ($page < 0) {
		$page = 0;
	}
	$events = $db->getEvents ($page);

	print_header ('/', 'じょそこん', '');

	echo "<nav class='items'>";
	echo "<ul>";

	if ($page > 0) {
		\printf ("<li><a href='/?page=%d'>前のページ</a></li>", $page - 1);
	}

	if (Session::isLoggedIn ()) {
		echo "<li><a href='/new/'>新規作成</a></li>";
	}

	foreach ($events as $event) {
		\printf ("<li><a href='/%s'>%s</a></li>"
			, escape ($event->name), escape ($event->title));
	}
	
	if (\count ($db->getEvents ($page + 1)) > 0) {
		\printf ("<li><a href='/?page=%d'>次のページ</a></li>", $page + 1);
	}

	echo "</ul>";
	echo "</nav>";
	echo "<p><span class='square'>";
	echo "<iframe src='https://twitter.menherausercontent.org/tweets.xhtml#ut_josocon'/>";
	echo "</span></p>";

	print_footer ();

} else {
	$event = $db->getEventByName ($path);
	if (!$event) {
		http_status (404);
		print_header ('/' . $path, 'ページが見つかりませんでした', '');

		print_footer ();
	} else {
		print_header ('/' . $path, $event->title);
		if (Session::isLoggedIn ()) {
			echo "<menu class='page-menu'>";
			\printf ("<li><a href='%s'>ページを編集</a></li>", escape ('/edit/?name=' . \urlencode ($event->name)));
			\printf ("<li><a href='%s'>ページを削除</a></li>", escape ('/delete/?name=' . \urlencode ($event->name)));
			\printf ("<li><a href='%s'>項目を追加</a></li>", escape ('/add-item/?event=' . \urlencode ($event->name)));
			echo "</menu>";
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
			\printf ('<josocon-markdown>%s</josocon-markdown>'
				, escape ($item->description));
			
			echo "<ul class='item-pictures'>";
			$pictures = $db->getItemPictures ($item);
			foreach ($pictures as $picture) {
				echo "<li><figure>";
				\printf ("<div class='widescreen'><img src='%s'/></div>"
					, escape ($picture->uri));
				
				if (Session::isLoggedIn ()) {
					echo "<menu class='item-picture-menu'>";
					\printf ("<li><a href='/delete-item-picture/?id=%d'>写真の削除…</a></li>"
						, $picture->id);
					echo "</menu>";
				}
				
				\printf ("<figcaption><josocon-markdown>%s</josocon-markdown></figcaption>"
					, escape ($picture->description));
				echo "</figure></li>";
			}
			echo "</ul>";
			
			if ($event->vote_status) {
				echo "<div class='vote-button'>";
				\printf ("<a href='/vote/?id=%d'>投票する</a>", $item->id);
				echo "</div>";
			}
			
			if (Session::isLoggedIn ()) {
				echo "<menu class='item-menu'>";
				\printf ("<li><a href='/edit-item/?id=%d'>項目の編集…</a></li>", $item->id);
				\printf ("<li><a href='/add-item-picture/?item=%d'>写真の追加…</a></li>", $item->id);
				\printf ("<li><a href='/delete-item/?id=%d'>項目の削除…</a></li>", $item->id);
				echo "</menu>";
				
				echo "<h4>関係者向け情報</h4>";
				echo '<p>現在の得票数：', escape ($item->vote_count), '</p>';
			}
			
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

