<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon;

/**
	サイトの構造を単純化するための簡素なテンプレートエンジン。
	
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

require_once __DIR__ . '/autoload.php';


const ORIGIN = 'https://www.josocon.u-tokyo.eu.org';
const SITE_NAME = '東大女装子コンテスト';
const SITE_IMAGE = 'https://www.josocon.u-tokyo.eu.org/resources/item.png';
const AVAILABILITY = true;

const DB_PATH = __DIR__ . '/../../db/josocon-db-v1.sqlite3';

const SITE_NOTICE = 'site-notice';


Session::init ();


/** 出力に必ず使用すること。 */
function escape ($text = '')
{
	return \htmlspecialchars ($text, \ENT_HTML5 | \ENT_DISALLOWED, 'UTF-8');
}

function http_status ($code)
{
	\header ("{$_SERVER['SERVER_PROTOCOL']} $code");
}

function set_cookie ($name, $value, $max_age = 0)
{
	$expire = $max_age < 1 ? 0 : (int) (time () + $max_age);
	\setcookie ($name, $value, $expire, '/', '', true, true);
}

/** ヘッダ部。 */
function print_header ($uri, $title, $postfix = ' | 東大女装子コンテスト')
{
	\header ('Server: utjosocon');
	\header ('Content-Type: application/xhtml+xml;  charset=UTF-8');
	\header ('X-Content-Type-Options: nosniff');
	
	$full_title = $title . $postfix;
	$full_uri = ORIGIN . $uri;
	$description = '東大女装子コンテスト実行委員会';
	$site_name = SITE_NAME;
	
	// サイトナビゲーション
	$navigation = [
		'/' => '新着情報',
		//'/contestants/' => '出場者',
		'/join-us/' => 'スタッフ募集',
		'/contact/' => 'お問い合わせ',
		'/login/' => '関係者向け',
	];

	$db = new DB (DB_PATH);
	$page = $db->getEventByName (SITE_NOTICE);
	if (!$page) {
		$site_notice_text = 'サイト準備中';
	} else {
		$site_notice_text = $page->description;
	}
	
	$site_notice = '';
	if (isset ($_SESSION['user'])) {
		$site_notice .= \sprintf (' (%sとしてログイン中)', $_SESSION['user']);
	}
	
	?>
<!--
________________________________________________________________________________

東大女装子コンテスト実行委員会2017-2019
Web開発者募集！
初心者可・経験者歓迎・プログラミング知識不問・グラフィックデザインやタイポグラフィに興味がある人歓迎
連絡先 Twitter: @_uts2
________________________________________________________________________________
-->
<html itemscope='itemscope' itemtype='http://schema.org/Article' lang='ja' xml:lang='ja' xmlns='http://www.w3.org/1999/xhtml' class='removed'><head prefix='og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#'><?php

echo '<title>', escape ($full_title), '</title>';

echo "<meta name='robots' content='noarchive, nostore'/>";

echo "<meta name='viewport' content='width=device-width, initial-scale=1'/>";
echo "<link rel='stylesheet' href='/resources/root.css'/>";
echo "<link rel='preload' href='/resources/template-page.css' as='style'/>";

echo "<link rel='icon' href='/resources/icon.png' type='image/png'/>";
echo "<link rel='manifest' href='/resources/app.webmanifest'/>";
echo "<link rel='apple-touch-icon' href='/resources/app-icon_192.png'/>";


// Meta properties
?><meta itemprop='name' content='<?= escape ($full_title) ?>'/><?php
?><meta itemprop='image' content='<?= escape (SITE_IMAGE) ?>'/><?php

?><meta property='article:published_time' content='<?= escape (time ()) ?>'/><?php
?><meta property='og:title' content='<?= escape ($full_title) ?>'/><?php
echo "<meta property='og:type' content='article'/>";
?><meta property='og:url' content='<?= escape ($full_uri) ?>'/><?php
?><meta property='og:image' content='<?= escape (SITE_IMAGE) ?>'/><?php
?><meta property='og:description' content='<?= escape ($description) ?>'/><?php
?><meta property='og:site_name' content='<?= escape ($site_name) ?>'/><?php


// scripts
//echo "<script src='/resources/webcomponents-bundle@2.3.0.js'/>";
echo "<script src='/resources/markdown-it_10.0.0.min.js'/>";

echo "</head>";

echo "<body><iframe hidden='' srcdoc='&lt;!doctype html&gt;&lt;script type=&apos;module&apos; src=&apos;/resources/root.mjs&apos;&gt;&lt;/script&gt;'></iframe><josocon-page>";

?><div slot='page-notice'><josocon-markdown><?= escape ($site_notice_text) ?></josocon-markdown><?= escape ($site_notice) ?><?php
if (isset ($_SESSION['user'])) {
	echo " <a href='/logout/'>ログアウト</a>";
}
echo "</div>";

?><div slot='page-title'><?= escape ($title) ?></div><?php
echo "<div slot='page-content'>";
	
}

/** フッタ部。 */
function print_footer ()
{
echo "</div></josocon-page></body></html>";
}


