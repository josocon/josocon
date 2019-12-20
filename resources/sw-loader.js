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

(async () => {
	if (!('serviceWorker' in navigator)) {
		console.log ('Service workers are not supported.');
		return false;
	}
	
	let registration = await navigator.serviceWorker.getRegistration ('/');
	console.log ('registration:', registration);
	
	if (registration) {
		return;
	}
	
	try {
		console.log ('trying to register');
		registration = await navigator.serviceWorker
		.register ('/resources/sw.js', {scope: '/'});
		console.log ('register returned');
	} catch (error) {
		console.log ('Service worker registration failed:', error);
	}
}) ();

