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
	
	$nonce = Session::getNonce ();
	$token = Session::getToken ($nonce);
	
	
	// RSA quiz
	
	$bits = 48;
	$min_bits = 45;
	
	do {
		do {
			$p = \gmp_random_bits (\mt_rand ($min_bits, $bits));
		} while (0 == \gmp_prob_prime ($p));
		
		do {
			$q = \gmp_random_bits (\mt_rand ($min_bits, $bits));
		} while (0 == \gmp_prob_prime ($q));
	} while (0 != \gmp_cmp (1, \gmp_gcd ($p, $q)));
	
	$pq = \gmp_mul ($p, $q);
	
	Session::set ('vote_pq', \gmp_strval ($pq));
	
	print_header ('/vote/', $item->name);
	?>
<josocon-markdown>
現在投票処理中です…（しばらくお待ちください）

**完了前に画面を閉じると投票が無効になります**
</josocon-markdown>
<section class='form-wrapper edit-form-wrapper'>
<p>投票を公正に行うために、以下の問題を計算中です。手動で答えを入力することで、この画面をスキップすることもできます（任意）。入力しない場合は、自動で計算が終了するまでお待ちください。</p>
<p><strong id='vote_semiprime'><?= escape (\gmp_strval ($pq)) ?></strong> = pq であるような p, q を求めよ。 (p, q は互いに素である 2 以上の整数)</p>
<form id='vote_proof_form' class='edit-form input-form' action='/vote/' method='POST'>
<input type='hidden' name='action' value='vote_proof'/>
<input type='hidden' name='nonce' value='<?= escape ($nonce) ?>'/>
<input type='hidden' name='token' value='<?= escape ($token) ?>'/>
<input type='hidden' name='id' value='<?= $item->id ?>'/>
<label for='vote_p'>p = </label>
<input class='input-field' id='vote_p' type='text' name='vote_p' placeholder='（2以上の整数）'/>
<label for='vote_q'>q = </label>
<input class='input-field' id='vote_q' type='text' name='vote_q' placeholder='（2以上の整数）'/>
<label for='vote_gcd'>gcd(p, q) = </label>
<input class='input-field' id='vote_gcd' type='text' name='vote_gcd' readonly='' placeholder='（最大公約数）'/>
<div class='submit'><button disabled=''>計算中…</button></div>
</form>
</section>
<?php
	print_footer ();
	
} elseif ('vote_proof' == $action) {
	$id = (int) ($_POST['id'] ?? '0');
	$nonce = $_POST['nonce'] ?? '';
	$token = $_POST['token'] ?? '';
	$p = $_POST['vote_p'] ?? '2';
	$q = $_POST['vote_q'] ?? '3';
	
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
	
	$pq = \gmp_mul ($p, $q);
	if (0 != \gmp_cmp (Session::get ('vote_pq'), $pq)) {
		http_status (400);
		print_header ('/vote/', 'エラー');
		echo "<josocon-markdown>解答が間違っています。</josocon-markdown>";
		print_footer ();
		exit;
	}
	
	if (0 < \gmp_cmp (2, $p) || 0 < \gmp_cmp (2, $q)) {
		http_status (400);
		print_header ('/vote/', 'エラー');
		echo "<josocon-markdown>2以上の整数を解答してください。</josocon-markdown>";
		print_footer ();
		exit;
	}
	
	if (0 != \gmp_cmp (1, \gmp_gcd ($p, $q))) {
		http_status (400);
		print_header ('/vote/', 'エラー');
		echo "<josocon-markdown>解答が間違っています。</josocon-markdown>";
		print_footer ();
		exit;
	}
	
	Session::set ("voted-{$event->id}", '1');
	$item->vote_count += 1;
	$db->updateItem ($item);
	
	\header (\sprintf ("location: /%s", \rawurlencode ($event->name)));
} else {
$id = $_GET['id'] ?? '';

if ($id === '') {
	throw new \Exception ('item not found');
}

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

$shortLivedToken = Session::getShortLivedToken ("vote_" . $id);
if (!\hash_equals ($shortLivedToken, $_GET['token'] ?? '')) {
	// expired link
	$event = $db->getEventById ($item->event_id);
	$path = $event->name ?? '';
	\header (\sprintf ("location: /%s", \rawurlencode ($path)));
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

