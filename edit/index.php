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
if (!Session::isLoggedIn ()) {
	throw new \Exception ('Must be logged in to do this');
} elseif ('edit' === $action) {
	$id = (int) ($_POST['id'] ?? '0');
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	if (!Session::verifyToken ($nonce, $token)) {
		throw new \Exception ('invalid token');
	}
	if (!$id) {
		throw new \Exception ('invalid id');
	}
	$name = $_POST['name'] ?? '';
	$title = $_POST['title'] ?? '';
	$text = $_POST['text'] ?? '';
	$vote_status = (int) ($_POST['vote_status'] ?? '0');
	if ('' === $title) {
		$title = $name;
	}
	if ('' === $name) {
		throw new \Exception ('invalid name');
	}
	// TODO: seperate validation into a class
	$reserved = ['login', 'logout', 'new', 'edit', 'delete', 'git'];
	if (\in_array ($name, $reserved, true)) {
		throw new \Exception ('reserved name');
	}
	$event = $db->getEventById ($id);
	if (!$event) {
		throw new \Exception ('page not found');
	}
	$event->title = $title;
	$event->name = $name;
	$event->description = $text;
	$event->vote_status = $vote_status;
	$db->updateEvent ($event);
	\header (\sprintf ("location: /%s", \rawurlencode ($name)));
} else {
$name = $_GET['name'] ?? '';

$event = $db->getEventByName ($name);
$nonce = Session::getNonce ();
$token = Session::getToken ($nonce);
print_header ('/login/', '編集');
?>
<section class='form-wrapper edit-form-wrapper'>
<h2>ページの編集</h2>
<form class='edit-form input-form' action='/edit/' method='POST'>
<input type='hidden' name='action' value='edit'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<input type='hidden' name='id' value='<?= $event->id ?>'/>
<label for='edit-name'>名前：/</label>
<input class='input-field' id='edit-name' type='text' name='name' value='<?= escape ($event->name) ?>'/>
<label for='edit-title'>タイトル：</label>
<input class='input-field' id='edit-title' type='text' name='title' value='<?= escape ($event->title) ?>'/>
<label for='edit-text'>本文： <br/>(<a href='https://ja.wikipedia.org/wiki/Markdown'>Markdown 形式</a>)</label>
<textarea class='input-field' id='edit-text' name='text'><?= escape ($event->description) ?></textarea>
<label for='edit-vote_status'>投票状態：</label>
<select class='input-field' id='edit-vote_status' name='vote_status'>
<option value='0'<?= $event->vote_status ? '' : " selected='selected'" ?>>無効</option>
<option value='1'<?= !$event->vote_status ? '' : " selected='selected'" ?>>有効</option>
</select>
<div class='submit'><button>編集実行</button></div>
</form>
</section>
<?php
print_footer ();
}
\ob_flush ();
} catch (\Throwable $e) {
	\ob_clean ();
	http_status (500);
	print_header ('/' . $path, 'エラー', '');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}

