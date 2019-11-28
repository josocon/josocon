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


let notificationUnsupported = false;
const notify = msg => {
	if (!('Notification' in window)) {
		if (!notificationUnsupported) {
			notificationUnsupported = true;
			console.warn ('Notification not supported:', msg);
			alert (msg || 'Notification not supported');
		} else if (msg) {
			console.warn ('Notification not supported:', msg);
			alert (msg);
		}
	} else if (Notification.permission === 'granted') {
		if (!msg) return;
		const notification = new Notification (msg);
	} else {
		(Notification.requestPermission () || {then () {}})
		.then (permission => {
			if (permission === 'granted') {
				const notification = new Notification (msg || 'Notification enabled!');
			} else {
				console.warn ('Notification denied:', msg);
				if (msg) {
					alert (msg);
				}
			}
		});
	}
};


let lastNonce = '';
let sendMessageToWorker = () => void 0;

const workerMessageListener = ev => {
	console.log ('message from shared worker:', ev.data);
	
	const vote_proof_form = document.getElementById ('vote_proof_form');
	const vote_p = vote_proof_form && vote_proof_form.vote_p;
	const vote_q = vote_proof_form && vote_proof_form.vote_q;
	const vote_gcd = vote_proof_form && vote_proof_form.vote_gcd;
	const vote_progress = vote_proof_form && vote_proof_form.querySelector ('.submit button');
	
	if (!ev.data) {
		console.log ('Empty message from shared worker');
		return;
	}
	
	if ('factors' == ev.data.type) {
		if (ev.data.nonce != '') {
			lastNonce = ev.data.nonce;
		}
		console.log ('factors:', ... ev.data.factors, 'computed in:', ev.data.duration, 'ms');
		if (vote_p) {
			vote_p.value = ev.data.factors[0] || '1';
		}
		if (vote_q) {
			vote_q.value = ev.data.factors[1] || '1';
		}
		const gcd = bigInt.gcd (... ev.data.factors);
		if (vote_progress) {
			vote_progress.textContent = 'Factored in ' + ev.data.duration + 'ms; Voting...';
		}
		if (vote_gcd) {
			vote_gcd.value = gcd;
		}
	} else if ('voted' == ev.data.type) {
		console.log ('Vote succeeded!');
		if (vote_progress) {
			vote_progress.textContent = 'Voted';
		}
		if (ev.data.loadUri) {
			setTimeout (() => navigate (ev.data.loadUri), 1000);
		}
		if ((!ev.data.notified) && ev.data.name) {
			notify ('Voted: ' + ev.data.name);
		}
	} else if ('vote_error' == ev.data.type) {
		console.error ('Error during vote:', ev.data.msg);
		if (vote_progress) {
			vote_progress.textContent = 'Failed';
		}
		notify ('Error during vote');
	} else {
		console.log ('Unknown message from shared worker:', ev.data);
	}
};

try {
	const mainWorker = new SharedWorker ('/resources/shared-worker.js');
	mainWorker.onerror = e => {
		console.error (e);
	};
	
	mainWorker.port.addEventListener ('message', workerMessageListener);
	mainWorker.port.start ();
	sendMessageToWorker = msg => mainWorker.port.postMessage (msg);
} catch (e) {
	const dedicatedWorker = new Worker ('/resources/dedicated-worker.js');
	dedicatedWorker.onerror = e => {
		console.error (e);
	};
	
	dedicatedWorker.addEventListener ('message', workerMessageListener);
	sendMessageToWorker = msg => dedicatedWorker.postMessage (msg);
}


const shadowRoots = new WeakMap ();
const STATES_MAX = 100;
const STATES_STEP = 10;
const states = new Map ();
const resolveURI = uri => new URL (uri, location.href).href;
const hasState = uri => states.has (resolveURI (uri));
const setState = (uri, state) => {
	if (!hasState (uri) & states.size >= STATES_MAX) {
		let i = 0;
		for (let [key, value] of states) {
			states.delete (key);
			i++;
			if (i >= STATES_STEP) {
				break;
			}
		}
	}
	states.set (resolveURI (uri), state);
};
const getState = uri => states.get (resolveURI (uri));

const saveState = uri => {
	let state;
	if (hasState (uri)) {
		state = getState (uri);
	} else {
		state = Object.create (null);
	}
	state.scrollX = window.scrollX;
	state.scrollY = window.scrollY;
	
	setState (uri, state);
};

const restoreState = uri => {
	let state;
	if (hasState (uri)) {
		state = getState (uri);
	} else {
		state = Object.create (null);
		state.scrollX = 0;
		state.scrollY = 0;
	}
	
	scrollTo (state.scrollX, state.scrollY);
};

const md = markdownit ();

const templatesPromise = (async () => {
	const res = await fetch ('/resources/templates.xhtml');
	const type = res.headers.get ('content-type').split (';')[0].trim ();
	return new DOMParser ().parseFromString (await res.text (), type);
}) ();

const getTemplate = async id => {
	const templates = await templatesPromise;
	return templates.getElementById (id);
};

let backButton;
const navigation = [location.href];
const updateBackButton = () => {
	if (!backButton) return;
	if (navigation.length < 2) {
		backButton.disabled = true;
	} else {
		backButton.disabled = false;
	}
};

const loadedCallback = () => {
	const vote_semiprime = document.getElementById ('vote_semiprime');
	const vote_proof_form = document.getElementById ('vote_proof_form');
	const formData = vote_proof_form ? new FormData (vote_proof_form) : new FormData;
	const params = new URLSearchParams (formData).toString ();
	const target = vote_proof_form && vote_proof_form.getAttribute ('action');
	const vote_progress = vote_proof_form && vote_proof_form.querySelector ('.submit button');
	
	if (vote_proof_form && target) {
		vote_proof_form.addEventListener ('change', ev => {
			const p = vote_proof_form.vote_p && vote_proof_form.vote_p.value;
			const q = vote_proof_form.vote_q && vote_proof_form.vote_q.value;
			if (p && q) {
				const gcd = bigInt.gcd (p, q);
				if (vote_proof_form.vote_gcd) {
					vote_proof_form.vote_gcd.value = gcd;
				}
				
				if (vote_proof_form.nonce.value == lastNonce) {
					return;
				}
				if (gcd.equals (1)) {
					sendMessageToWorker ({type: 'voted'});
					if (vote_progress) {
						vote_progress.textContent = 'Voting...';
					}
					setTimeout (() => vote_proof_form.submit (), 1000);
				} else if (vote_progress) {
					vote_progress.textContent = 'Incorrect';
				}
			}
		});
	}
	if (vote_semiprime && target) {
		const input = vote_semiprime.textContent;
		console.log ('Sending command to factor an integer:', input, params, target);
		sendMessageToWorker ({type: 'pf', input, params, target});
		if (vote_progress) {
			vote_progress.textContent = 'Computing...';
		}
	}
};

const errorPage = (page, msg) => {
	console.error (msg);
	
	if (!page) {
		return false;
	}
	
	const notice = document.createElement ('div');
	notice.slot = 'page-notice';
	notice.textContent = 'Failed to load.';
	
	const title = document.createElement ('div');
	title.slot = 'page-title';
	title.textContent = 'Error';
	
	const content = document.createElement ('div');
	content.slot = 'page-content';
	const paragraph = document.createElement ('p');
	paragraph.textContent = String (msg);
	content.appendChild (paragraph);
	
	page.textContent = '';
	page.appendChild (notice);
	page.appendChild (title);
	page.appendChild (content);
	
	document.title = 'Error';
	
	return true;
};

const loadPage = async (... fetchArgs) => {
	try {
		const res = await fetch (... fetchArgs);
		
		const type = res.headers.get ('content-type').split (';')[0].trim ();
		const doc = new DOMParser().parseFromString(await res.text(), type);
		console.log ('fetched document:', doc);
		
		const newPage = doc.getElementsByTagName ('josocon-page')[0];
		const page = document.getElementsByTagName ('josocon-page')[0];
		if (!newPage) {
			errorPage (page, 'invalid document');
			return res;
		}
		if (!page) {
			console.error ('not supported');
			return res;
		}
		
		page.textContent = '';
		
		[... newPage.childNodes]
		.map (node => document.adoptNode (node))
		.forEach (node => page.appendChild (node));
		
		document.title = doc.title;
		
		return res;
	} catch (error) {
		const page = document.getElementsByTagName ('josocon-page')[0];
		errorPage (page, error);
	}
};

const navigate = async (uri, formData) => {
	if (new URL (uri, location.href).host !== location.host) {
		throw new TypeError ('Navigation target must be on the same origin');
	}
	
	let method, fetchOptions;
	if (formData instanceof FormData) {
		method = 'POST';
		fetchOptions = {method, body: formData, credentials: 'same-origin'};
	} else if (formData instanceof URLSearchParams) {
		method = 'POST';
		fetchOptions = {method, body: formData, credentials: 'same-origin'};
	} else {
		method = 'GET';
		fetchOptions = {credentials: 'same-origin'};
	}
	
	const res = await loadPage (uri, fetchOptions);
	
	const target = new URL (res.url);
	
	const nonEmpty = navigation.filter (s => '' !== s);
	let prev = nonEmpty[nonEmpty.length - 1];
	
	if (prev === target.href) {
		while ('' === navigation[navigation.length - 1]) {
			navigation.pop ();
		}
	} else if ('' === target.search) {
		navigation.push (target.href);
	} else {
		navigation.push ('');
	}
	
	saveState (location.href);
	restoreState (target.href);
	
	history.replaceState ({}, "", target.href);
	updateBackButton ();
	
	loadedCallback ();
};

const back = async () => {
	if (navigation.length < 2) {
		return;
	}
	navigation.pop ();
	while ('' === navigation[navigation.length - 1]) {
		navigation.pop ();
	}
	
	const uri = navigation[navigation.length - 1];
	const res = await loadPage (uri, {credentials: 'same-origin'});
	
	saveState (location.href);
	restoreState (res.url);
	
	history.replaceState ({}, "", res.url);
	
	updateBackButton ();
	
	loadedCallback ();
};

customElements.define ('josocon-page', class extends HTMLElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'open' });
		console.log ('shadowRoot:', shadowRoot);
		shadowRoots.set (this, shadowRoot);
		//this.classList.add ('removed');
	}
	
	async load () {
		const root = shadowRoots.get (this);
		if (root.childNodes.length) return true;
		
		const template = await getTemplate ('template-page');
		const content = document.importNode (template.content, true);
		root.appendChild (content);
		//this.classList.remove ('removed');
		return true;
	}
	
	connectedCallback () {
		const root = shadowRoots.get (this);
		this.load ()
		.then (a => {
			console.log ('connected:', a);
			
			backButton = root.getElementById ('site-back-button');
			backButton.addEventListener ('click', ev => {
				back ();
			});
		})
		.catch (e => console.error (e));
	}
});

customElements.define ('josocon-markdown', class extends HTMLElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'open' });
		shadowRoots.set (this, shadowRoot);
	}
	
	render () {
		const shadowRoot = shadowRoots.get (this);
		const html = '<div>' + md.render(this.textContent) + '</div>';
		const node = new DOMParser ().parseFromString (html, 'text/html').body.children[0];
		const link = document.createElement ('link');
		link.rel = 'stylesheet';
		link.href = '/resources/common.css';
		shadowRoot.textContent = '';
		shadowRoot.appendChild (link);
		shadowRoot.appendChild (document.adoptNode (node));
	}
	
	connectedCallback () {
		this.render ();
		
		const observer = new MutationObserver (mutations => {
			this.render ();
		});
		
		observer.observe (this, {
			characterData: true,
			childList: true,
			subtree: true,
		});
	}
});

window.addEventListener ('load', e => {
	document.documentElement.classList.remove ('removed');
	console.log (decodeURIComponent ('________________________________________________________________________________%0A%0A%E6%9D%B1%E5%A4%A7%E5%A5%B3%E8%A3%85%E5%AD%90%E3%82%B3%E3%83%B3%E3%83%86%E3%82%B9%E3%83%88%E5%AE%9F%E8%A1%8C%E5%A7%94%E5%93%A1%E4%BC%9A2017-2019%0AWeb%E9%96%8B%E7%99%BA%E8%80%85%E5%8B%9F%E9%9B%86%EF%BC%81%0A%E5%88%9D%E5%BF%83%E8%80%85%E5%8F%AF%E3%83%BB%E7%B5%8C%E9%A8%93%E8%80%85%E6%AD%93%E8%BF%8E%E3%83%BB%E3%83%97%E3%83%AD%E3%82%B0%E3%83%A9%E3%83%9F%E3%83%B3%E3%82%B0%E7%9F%A5%E8%AD%98%E4%B8%8D%E5%95%8F%E3%83%BB%E3%82%B0%E3%83%A9%E3%83%95%E3%82%A3%E3%83%83%E3%82%AF%E3%83%87%E3%82%B6%E3%82%A4%E3%83%B3%E3%82%84%E3%82%BF%E3%82%A4%E3%83%9D%E3%82%B0%E3%83%A9%E3%83%95%E3%82%A3%E3%81%AB%E8%88%88%E5%91%B3%E3%81%8C%E3%81%82%E3%82%8B%E4%BA%BA%E6%AD%93%E8%BF%8E%0A%E9%80%A3%E7%B5%A1%E5%85%88%20Twitter%3A%20%40_uts2%0A________________________________________________________________________________'));
});

document.addEventListener ('click', ev => {
	const composedPath = ev.composedPath ();
	for (let target of composedPath) {
		//console.log (target);
		if (!target.tagName || 'a' !== target.tagName.toLowerCase ()) {
			continue;
		}
		
		if (!target.href) {
			continue;
		}
		
		ev.preventDefault ();
		
		notify ();
		
		const action = new URL (target.href, location.href);
		console.log (action);
		if (action.host !== location.host) {
			window.open (action.href, '_blank');
		} else {
			navigate (action.href);
		}
		return;
	}
});

document.addEventListener ('submit', ev => {
	const composedPath = ev.composedPath ();
	for (let target of composedPath) {
		//console.log (target);
		if (!target.tagName || 'form' !== target.tagName.toLowerCase ()) {
			continue;
		}
		
		if (!target.hasAttribute ('action')) {
			continue;
		}
		
		if (target.classList.contains ('form-direct')) {
			continue;
		}
		
		ev.preventDefault ();
		
		const action = new URL (target.getAttribute ('action'), location.href);
		console.log (action);
		if (action.host !== location.host) {
			console.error ('cross-origin forms not supported');
			return;
		} else {
			const formData = new FormData (target);
			navigate (action.href, formData);
		}
		return;
	}
});

