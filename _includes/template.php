<?php /* -*- tab-width: 4; indent-tabs-mode: t -*-
vim: ts=4 noet ai */

namespace josocon;

/**
	サイトの構造を単純化するための簡素なテンプレートエンジン。
	
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

const ORIGIN = 'https://www.josocon.u-tokyo.eu.org';
const SITE_NAME = '東大女装子コンテスト';
const SITE_IMAGE = 'https://www.josocon.u-tokyo.eu.org/resources/item.png';
const AVAILABILITY = false;


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

	if (AVAILABILITY) {
		$site_notice = '';
	} else {
		$site_notice = 'サイト準備中';
	}
	
	?>
<!--
________________________________________________________________________________

東大女装子コンテスト実行委員会2017-2019
Webデザインアシスタント募集！
初心者可・プログラミング知識不問・グラフィックデザインやタイポグラフィに興味がある人歓迎
連絡先 Twitter: @_uts2
________________________________________________________________________________
-->

<html itemscope='itemscope' itemtype='http://schema.org/Article' lang='ja' xml:lang='ja' xmlns='http://www.w3.org/1999/xhtml'>
<head prefix='og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#'>
<title><?= escape ($full_title) ?></title>

<link rel='icon' href='/resources/icon.png' type='image/png'/>

<meta itemprop='name' content='<?= escape ($full_title) ?>'/>
<meta itemprop='image' content='<?= escape (SITE_IMAGE) ?>'/>

<meta property='article:published_time' content='<?= escape (time ()) ?>'/>
<meta property='og:title' content='<?= escape ($full_title) ?>'/>
<meta property='og:type' content='article'/>
<meta property='og:url' content='<?= escape ($full_uri) ?>'/>
<meta property='og:image' content='<?= escape (SITE_IMAGE) ?>'/>
<meta property='og:description' content='<?= escape ($description) ?>'/>
<meta property='og:site_name' content='<?= escape ($site_name) ?>'/>

<meta name='twitter:card' content='summary'/>
<meta name='twitter:site' content='@ut_josocon'/>
<meta name='twitter:title' content='<?= escape ($full_title) ?>'/>
<meta name='twitter:description' content='<?= escape ($description) ?>'/>
<meta name='twitter:image' content='<?= escape (SITE_IMAGE) ?>'/>

<meta name='viewport' content='width=device-width, initial-scale=1'/>

<link rel='stylesheet' href='/resources/root.css'/>
<script src='/resources/markdown-it_10.0.0.min.js'/>
<script src='/resources/root.mjs'/>
</head>
<body is='josocon-page'>
<div slot='page-notice'><?= escape ($site_notice) ?></div>
<div slot='page-title'><?= escape ($title) ?></div>
<div slot='page-content'>
<?php
	
}

/** フッタ部。 */
function print_footer ()
{
	//
	?>
</div>
</body>
</html>
<?php
}

