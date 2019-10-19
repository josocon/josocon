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
	$text = "12345";

	\header ('content-type: image/png');
	
	$im = \imagecreate (256, 64);
	
	$bg = \imagecolorallocate ($im, 255, 255, 255);
	$textcolor = \imagecolorallocate ($im, 0, 0, 0);
	
	\imagestring ($im, 5, 24, 64, $text, $textcolor);
	$im = \imagerotate ($im, \mt_rand (0, 60) - 30, $bg);
	\imagepng ($im);
	\imagedestroy ($im);
} catch (\Throwable $e) {
	print_header ('/captcha/', 'エラー');
	\printf ('<pre>%s</pre>', escape ($e));
	print_footer ();
}
