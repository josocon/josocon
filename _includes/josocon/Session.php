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


class Session
{
	public static function init (): void
	{
		// TODO: too long gc maxlifetime may cause storage bloat
		$lifetime = 60 * 60 * 24 * 365 * 2;
		\ini_set ('session.use_strict_mode', 1);
		\ini_set ('session.gc_maxlifetime', $lifetime);
		\session_set_cookie_params ([
			'lifetime' => $lifetime,
			'path' => '/',
			'secure' => true,
			'httponly' => true,
			'samesite' => 'Lax',
		]);
		\session_name ('js');
		\session_start ();
		\setcookie (\session_name (), \session_id (), [
			'expires' => time () + $lifetime,
			'path' => '/',
			'secure' => true,
			'httponly' => true,
			'samesite' => 'Lax',
		]);
	}
	
	public static function start (string $name): void
	{
		$_SESSION['user'] = $name;
		$_SESSION['token'] = \bin2hex (\random_bytes (16));
	}
	
	public static function getUserName (): string
	{
		return $_SESSION['user'] ?? '';
	}
	
	public static function isLoggedIn (): bool
	{
		return isset ($_SESSION['user']);
	}
	
	public static function logOut (): bool
	{
		unset ($_SESSION['user']);
	}
	
	public static function getNonce (): string
	{
		// assuming 64-bit (or more)
		return (string) ((int) (1000 * \microtime (true)));
	}
	
	public static function getToken (string $nonce): string
	{
		return \hash_hmac ('sha512', $nonce, $_SESSION['token'] ?? \random_bytes (16));
	}
	
	public static function verifyToken (string $nonce, string $token): bool
	{
		return \hash_equals (self::getToken ($nonce), $token);
	}
	
	public static function set (string $name, string $value): void
	{
		$_SESSION["set_{$name}"] = $value;
	}
	
	public static function get (string $name): string
	{
		return $_SESSION["set_{$name}"] ?? '';
	}
}

