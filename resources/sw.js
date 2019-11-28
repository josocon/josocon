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


const CACHE_VERSION = 8;
const CACHE_MAIN = 'cache-main-v' + CACHE_VERSION;

const SKELETON = '/resources/empty.xhtml';
const PRECACHE = [
	SKELETON,
	'/resources/root.css',
	'/resources/root.mjs',
	'/resources/shared-worker.js',
	'/resources/dedicated-worker.js',
	'/resources/worker-commin.js',
	'/resources/markdown-it_10.0.0.min.js',
	'/resources/BigInteger.min.js',
	'/resources/common.css',
	'/resources/templates.xhtml',
	'/resources/template-page.css',
	'/resources/icon.png',
	'/resources/isshin-map.jpg',
	'https://fonts.menherausercontent.org/mplus-1-light-sub.woff',
	'https://fonts.menherausercontent.org/mplus-1-medium-sub.woff',
];

self.addEventListener ('install', ev => {
	console.log ('install');
	ev.waitUntil (preload ());
});

const preload = async () => {
	console.log ('scope:', self.registration.scope);
	console.log ('origin:', self.location.origin);
	console.log ('caches:', typeof caches, self.caches);
	
	const testAddress = new URL ('/', location.origin).href;
	fetch (testAddress)
	.then (res => console.log ('fetch:', res))
	.catch (e => console.log ('fetch:', e));
	
	const cache = await caches.open (CACHE_MAIN);
	try {
		console.log ('cache:', cache);
		const res = await cache.addAll (PRECACHE);
		console.log ('preload succeeded');
		return res;
	} catch (e) {
		console.log ('preload failed', e);
		throw e;
	}
};

self.addEventListener ('message', ev => console.log (ev));

self.addEventListener ('activate', function (event) {
	event.waitUntil (
		caches.keys ().then (async cacheNames => {
			return Promise.all (
				cacheNames.map (function (cacheName) {
					if (cacheName !== CACHE_MAIN) {
						console.log ('Deleting out of date cache:', cacheName);
						return caches.delete (cacheName);
					}
				})
			);
		})
	);
});

self.addEventListener ('fetch', ev => {
	if (ev.request.method !== 'GET') {
		// POST, ignore
		console.log ('ignoring request');
		return void ev.respondWith (fetch (ev.request));
	}
	
	ev.respondWith (fromCache (ev.request));
	ev.waitUntil (update (ev.request));
});

const fromCache = async request => {
	const cache = await caches.open (CACHE_MAIN);
	let response = await cache.match (request);
	console.log ('matched response:', response);
	/*if (!response) {
		response = await cache.match ('/');
		console.log ('matched /:', response);
	}*/
	if (!response) {
		// not included in cache, ignoring
		try {
			response = await fetch (request);
		} catch (e) {
			console.log ('fetch failed:', e);
			response = await cache.match (SKELETON);
		}
	}
	//await cache.put (request, response);
	console.log ('returning response:', response);
	return response;
};

const update = async request => {
	const cache = await caches.open (CACHE_MAIN);
	let response = await cache.match (request);
	try {
		if (!response) {
			console.log ('skipping update:', request);
			return;
		}
		response = await fetch (request);
		return cache.put (request, response);
	} catch (err) {
		console.log (err);
	}
};

