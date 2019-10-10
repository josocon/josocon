/* -*- tab-width: 4; indent-tabs-mode: t -*- */
/*
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
*/

'use strict'; // for non-module scripts

const CACHE_MAIN = 'cache-main';
const PRECACHE = [
	'/',
	'/resources/root.css',
	'/resources/root.mjs',
	'/resources/markdown-it_10.0.0.min.js',
	'/resources/common.css',
	'/resources/templates.xhtml',
	'/resources/template-page.css',
	'/resources/icon.png',
	'/resources/isshin-map.png',
];

self.addEventListener ('install', ev => {
	console.log ('install');
	ev.waitUntil (preload ());
});

const preload = async () => {
	const cache = await caches.open (CACHE_MAIN);
	try {
		const res = await cache.addAll (PRECACHE);
		return res;
	} catch (e) {
		return false;
	}
};

self.addEventListener ('fetch', ev => {
	ev.respondWith (fromCache (ev.request));
	ev.waitUntil (update (ev.request));
});

const fromCache = async request => {
	const cache = await caches.open(CACHE_MAIN);
	const matching = await cache.match (request);
	return matching || Promise.reject ('no-match');
};

const update = async request => {
	const cache = await caches.open(CACHE_MAIN);
	try {
		const response = await fetch (request);
		return cache.put (request, response);
	} catch (err) {
		console.log (err);
	}
};

