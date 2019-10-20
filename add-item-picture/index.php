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
} elseif ('add' === $action) {
	$description = $_POST['description'] ?? '';
	$item_id = $_POST['item_id'] ?? '';
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	if (!Session::verifyToken ($nonce, $token)) {
		throw new \Exception ('invalid token');
	}
	$item = $db->getItemById ($item_id);
	if (!$item) {
		throw new \Exception ('item not found');
	}
	
	if (!isset ($_FILES['file']['type'])) {
		throw new \Exception ('file not found');
	}
	
	switch ($_FILES['file']['type']) {
		case 'image/png':
			$ext = '.png';
			break;
		
		case 'image/jpeg':
			$ext = '.jpg';
			break;
			
		default:
			throw new \Exception ('unsupported file type');
	}
	
	$hash = \hash_file ('sha256', $_FILES['file']['tmp_name']);
	$path = __DIR__ . '/../resources/uploads/' . $hash . $ext;
	if (!\move_uploaded_file ($_FILES['file']['tmp_name'], $path)) {
		throw new \Exception ('file check failed');
	}
	
	$uri = '/resources/uploads/' . $hash . $ext;
	
	$event = $db->getEventById ($item->event_id);
	$db->addItemPicture ($item, $uri, $description);
	\header (\sprintf ("location: /%s", \rawurlencode ($event->name)));
} else {
	$item = $db->getItemById ($_GET['item'] ?? '');
	if (!$item) {
		throw new \Exception ('item not found');
	}
	print_header ('/add-item-picture/', $item->name);
	$nonce = Session::getNonce ();
	$token = Session::getToken ($nonce);
?>
<section class='form-wrapper edit-form-wrapper'>
<h2>写真の追加</h2>
<form class='create-form input-form' action='/add-item-picture/' method='POST' enctype='multipart/form-data'>
<input type='hidden' name='action' value='add'/>
<input type='hidden' name='item_id' value='<?= escape ($item->id) ?>'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
<label for='create-file'>名前：</label>
<input class='input-field' id='create-file' type='file' name='file'/>
<label for='create-description'>説明：</label>
<textarea class='input-field' id='create-description' name='description'/>
<div class='submit'><button>写真追加</button></div>
</form>
</section>
<?php
print_footer ();
}
\ob_flush ();
} catch (\Throwable $e) {
	\ob_clean ();
	http_status (500);
	print_header ('/add-item/', 'エラー', '');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}

