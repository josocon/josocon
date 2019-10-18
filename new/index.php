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
} elseif ('create' === $action) {
	$name = $_POST['name'] ?? '';
	$title = $_POST['title'] ?? '';
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	if (!Session::verifyToken ($nonce, $token)) {
		throw new \Exception ('invalid token');
	}
	if ('' === $title) {
		$title = $name;
	}
	if ('' === $name) {
		throw new \Exception ('invalid name');
	}
	$reserved = ['login', 'logout', 'new', 'edit', 'delete', 'git'];
	if (\in_array ($name, $reserved, true)) {
		throw new \Exception ('reserved name');
	}
	$db->addEvent ($name, $title);
	\header (\sprintf ("location: /%s", \rawurlencode ($name)));
} else {
print_header ('/login/', '新規作成', '');
$nonce = Session::getNonce ();
$token = Session::getToken ($nonce);
?>
<section class='form-wrapper'>
<h2>ページの作成</h2>
<form class='create-form input-form' action='/new/' method='POST'>
<input type='hidden' name='action' value='create'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<label for='create-name'>名前：/</label>
<input class='input-field' id='create-name' type='text' name='name'/>
<label for='create-title'>タイトル：</label>
<input class='input-field' id='create-title' type='text' name='title'/>
<div class='submit'><button>ページ作成</button></div>
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

