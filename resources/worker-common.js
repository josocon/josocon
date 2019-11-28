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

importScripts ('/resources/BigInteger.min.js');
importScripts ('/resources/factorization.js');

const notify = msg => {
	if ('undefined' == typeof Notification) return;
	if ('granted' === Notification.permission) {
		const notification = new Notification (msg);
	}
};

const submitted = false;
const markAsSubmitted = () => void (submitted = true);
const resetSubmitted = () => void (submitted = false);
const isSubmitted = () => !!submitted;

const compute = async (port, data) => {
	const {input, target, params} = data;
	
	const start = +new Date;
	const factors = await factor (input);
	const end = +new Date;
	
	// run the event loop
	await Promise.resolve ();
	
	let id = 0;
	let nonce = '';
	if (target && params) {
		const query = new URLSearchParams (params);
		id = +query.get ('id');
		nonce = query.get ('nonce') || '';
	}
	port.postMessage ({type: 'factors', factors, duration: end - start, id, nonce});
	if (isSubmitted ()) {
		resetSubmitted ();
		return;
	}
	
	if (!target) {
		return;
	}
	
	let error = '';
	const query = new URLSearchParams (params);
	try {
		if (factors.length < 2) {
			throw new TypeError ('Failed to factor an integer');
		}
		query.set ('vote_p', factors[0]);
		query.set ('vote_q', factors[1]);
		
		const action = new URL (target, location.href).href;
		const res = await fetch (action, {
			method: 'POST',
			body: query,
			credentials: 'same-origin',
		});
		
		if (res.url == action) {
			error = await res.text ();
			console.error (error);
			throw new TypeError ('Internal error during vote');
		}
		
		notify ('Voted: ' + query.get ('name'));
		
		port.postMessage ({type: 'voted', loadUri: res.url});
	} catch (e) {
		port.postMessage ({type: 'vote_error', msg: String (e), error});
		throw e;
	}
};

