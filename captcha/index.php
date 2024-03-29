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
	$text = Session::get ('captcha-v1');
	if ('' === $text) {
		$text = \base64_encode (\random_bytes (6));
		$text = \strtr ($text, '0O', '_=');
		Session::set ('captcha-v1', $text);
	}

	\header ('content-type: image/png');
	
	$im = \imagecreatetruecolor (128, 32);
	\imagesetinterpolation ($im, \IMG_BICUBIC_FIXED);
	\imagesavealpha ($im, true);
	$transparent = \imagecolorallocatealpha ($im, 0, 0, 0, 127);
    \imagefill ($im, 0, 0, $transparent);
	$textcolor = \imagecolorallocate ($im, 0, 0, 0);
	
	$rand = function () {
		return \mt_rand () / \mt_getrandmax ();
	};
	\imagestring ($im, 5, 8, 8, $text, $textcolor);
	$im = \imagescale ($im, 256, 64);
	\imagesavealpha ($im, true);
	$im = \imageaffine ($im, [1 - $rand () / 4, $rand () / 4 - 0.125, $rand () / 4 - 0.125, 1 - $rand () / 4, 0, 0]);
	
	\imagepng ($im);
	\imagedestroy ($im);
} catch (\Throwable $e) {
	print_header ('/captcha/', 'エラー');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}
