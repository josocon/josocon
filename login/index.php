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
if (Session::isLoggedIn () && '' === $action) {
	$users = $db->getUsers ();
	print_header ('/' . $path, '管理画面', '');
	
	$info = <<<'EOF'
サイトの運用や仕組みに関しては[このページ](/policy)を参照してください。
EOF;
	echo "<h2>案内</h2>";
	\printf ('<josocon-markdown>%s</josocon-markdown>', escape ($info));
	
	echo "<h2>現在のブラウザ</h2>";
	\printf ('<pre>%s</pre>', escape ($_SERVER['HTTP_USER_AGENT'] ?? ''));
	
	echo "<h2>ユーザ一覧</h2>";
	$usersArray = [];
	foreach ($users as $user) {
		$obj = [];
		$obj['id'] = $user->id;
		$obj['name'] = $user->name;
		$obj['long_name'] = $user->long_name;
		$obj['description'] = $user->description;
		$usersArray[] = $obj;
	}
	\printf ('<pre>%s</pre>'
		, escape (\json_encode ($usersArray
			, \JSON_INVALID_UTF8_SUBSTITUTE
			| \JSON_PRETTY_PRINT
			| \ JSON_UNESCAPED_SLASHES
			| \JSON_UNESCAPED_UNICODE
			| \JSON_THROW_ON_ERROR)));
	
	echo "<h2>サーバ環境</h2>";
	echo "<table><tbody><tr>";
	echo "<th>", escape ('PHP ' . \PHP_VERSION), "</th>";
	echo "<td>", escape (\implode (', ', \get_loaded_extensions ())), "</td>";
	echo "</tr></tbody></table>";
	
	$nonce = Session::getNonce ();
	$token = Session::getToken ($nonce);
	
	?>
<section class='form-wrapper'>
<h2>全データのダウンロード</h2>
<form class='form-direct download-form input-form' action='/login/' method='POST'>
<input type='hidden' name='action' value='dump'/>
<div class='submit'><button>ダウンロード</button></div>
</form>
</section>
<section class='form-wrapper'>
<h2>ユーザー登録</h2>
<form class='signup-form input-form' action='/login/' method='POST'>
<input type='hidden' name='action' value='signup'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<label for='signup-name'>名前：</label>
<input class='input-field' id='signup-name' type='text' name='name'/>
<label for='signup-pass'>パスワード：</label>
<input class='input-field' id='signup-pass' type='password' name='pass'/>
<div class='submit'><button>登録</button></div>
</form>
</section>
<?php
	print_footer ();
} elseif (Session::isLoggedIn () && 'dump' === $action) {
	\header ('content-type: application/octet-stream');
	\header ('content-disposition: attachment; filename="josocon.sqlite3"');
	\readfile (DB_PATH);
} elseif ('signup' === $action) {
	
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	if (!Session::verifyToken ($nonce, $token)) {
		throw new \Exception ('invalid token');
	}
	
	$users = $db->getUsers ();
	if (\count ($users) > 0 && !Session::isLoggedIn ()) {
		throw new \Exception ('user already exists');
	}
	$name = $_POST['name'] ?? '';
	$password = $_POST['pass'] ?? '';
	if ('' === $name || '' === $password) {
		throw new \Exception ('invalid credentials');
	}
	$db->addUser ($name, $password);
	Session::start ($name);
	\header ('Location: /');
} elseif ('login' === $action) {
	$name = $_POST['name'];
	$password = $_POST['pass'];
	$user = $db->getUserByName ($name);
	if (!$user) {
		throw new \Exception ('user not found');
	}
	if (!$user->verifyPassword ($password)) {
		throw new \Exception ('invalid password');
	}
	Session::start ($user->name);
	\header ('Location: /');
} else {
//$site_notice = '2018年もじょそこんやります…更新中';
$users = $db->getUsers ();
print_header ('/login/', '関係者向けログイン', '');
?>
<section class='form-wrapper'>
<h2>ログイン</h2>
<form class='login-form input-form' action='/login/' method='POST'>
<input type='hidden' name='action' value='login'/>
<label for='login-name'>名前：</label>
<input class='input-field' id='login-name' type='text' name='name'/>
<label for='login-pass'>パスワード：</label>
<input class='input-field' id='login-pass' type='password' name='pass'/>
<div class='submit'><button>ログイン</button></div>
</form>
</section>
<?php
if (\count ($users) < 1) {

$nonce = Session::getNonce ();
$token = Session::getToken ($nonce);

?>
<section class='form-wrapper'>
<h2>ユーザー登録</h2>
<form class='signup-form input-form' action='/login/' method='POST'>
<input type='hidden' name='action' value='signup'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<label for='signup-name'>名前：</label>
<input class='input-field' id='signup-name' type='text' name='name'/>
<label for='signup-pass'>パスワード：</label>
<input class='input-field' id='signup-pass' type='password' name='pass'/>
<div class='submit'><button>登録</button></div>
</form>
</section>
<?php
}
echo "<h2>現在のブラウザ</h2>";
\printf ('<pre>%s</pre>', escape ($_SERVER['HTTP_USER_AGENT'] ?? ''));
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

