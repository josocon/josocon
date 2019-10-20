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

try {
\ob_start ();
$action = $_POST['action'] ?? '';
$db = new DB (DB_PATH);
if ('vote' === $action) {
	$id = (int) ($_POST['id'] ?? '0');
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	$captcha_text = $_POST['captcha'] ?? '';
	
	$text = Session::get ('captcha-v1');
	
	if (!$id) {
		throw new \Exception ('invalid id');
	}
	$item = $db->getItemById ($id);
	if (!$item) {
		throw new \Exception ('item not found');
	}
	$event = $db->getEventById ($item->event_id);
	if (!$event) {
		throw new \Exception ('event not found');
	}
	
	$voted = Session::get ("voted-{$event->id}");
	Session::set ("voted-{$event->id}", '1');
	
	Session::set ('captcha-v1', '');
	if ($captcha_text !== $text) {
		http_status (400);
		print_header ('/vote/', 'エラー');
		echo "<josocon-markdown>入力した文字が一致しません。もう一度やり直してください</josocon-markdown>";
		print_footer ();
		exit;
	}
	
	if ($voted) {
		http_status (400);
		print_header ('/vote/', 'エラー');
		echo "<josocon-markdown>すでにこの投票は投票済みです。</josocon-markdown>";
		print_footer ();
		exit;
	}
	
	if (!Session::verifyToken ($nonce, $token)) {
		throw new \Exception ('invalid token');
	}
	$item->vote_count += 1;
	$db->updateItem ($item);
	
	\header (\sprintf ("location: /%s", \rawurlencode ($event->name)));
} else {
$id = $_GET['id'] ?? '';

$item = $db->getItemById ($id);
if (!$item) {
	throw new \Exception ('item not found');
}

$voted = Session::get ("voted-{$item->event_id}");
if ($voted) {
	http_status (400);
	print_header ('/vote/', 'エラー');
	echo "<josocon-markdown>すでにこの投票は投票済みです。</josocon-markdown>";
	print_footer ();
	exit;
}

$nonce = Session::getNonce ();
$token = Session::getToken ($nonce);
print_header ('/vote/', $item->name);
?>
<josocon-markdown>
1回のイベントにつき一度しか投票できません。投票しますか？
</josocon-markdown>
<section class='form-wrapper edit-form-wrapper'>
<h2>投票</h2>
<img class='captcha-img' src='/captcha/'/>
<form class='edit-form input-form' action='/vote/' method='POST'>
<input type='hidden' name='action' value='vote'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<input type='hidden' name='id' value='<?= $item->id ?>'/>
<label for='edit-text'>画像の文字を入力：</label>
<input class='input-field' id='edit-text' type='text' name='captcha' placeholder='（画像上に見える文字を入力）'/>
<div class='submit'><button>投票実行</button></div>
</form>
</section>
<?php
print_footer ();
}
\ob_flush ();
} catch (\Throwable $e) {
	\ob_clean ();
	http_status (500);
	print_header ('/vote/', 'エラー');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}

