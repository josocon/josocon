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
if ('signup' === $action) {
	$db = new DB (DB_PATH);
	$name = $_POST['name'];
	$password = $_POST['pass'];
	$db->addUser ($name, $password);
	\header ('Location: /');
} elseif ('login' === $action) {


} else {
//$site_notice = '2018年もじょそこんやります…更新中';
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
<section class='form-wrapper'>
<h2>ユーザー登録</h2>
<form class='signup-form input-form' action='/login/' method='POST'>
<input type='hidden' name='action' value='signup'/>
<label for='signup-name'>名前：</label>
<input class='input-field' id='signup-name' type='text' name='name'/>
<label for='signup-pass'>パスワード：</label>
<input class='input-field' id='signup-pass' type='password' name='pass'/>
<div class='submit'><button>登録</button></div>
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

