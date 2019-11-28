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

importScripts ('/resources/worker-common.js');

onconnect = ev => {
	const [port] = ev.ports;
	
	port.addEventListener ('message', ev => {
		if (ev.data && ev.data.type) {
			switch (ev.data.type) {
				case 'pf':
					compute (port, ev.data)
					.then (() => {
						console.log ('Computation completed');
					})
					.catch (e => {
						console.error (e);
					});
					break;
				
				case 'voted':
					markAsSubmitted ();
					break;
			}
		}
	});
	
	port.start ();
};

