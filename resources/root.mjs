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


const shadowRoots = new WeakMap ();

const md = markdownit ();

const templatesPromise = (async () => {
	const res = await fetch ('/resources/templates.xhtml');
	const type = res.headers.get ('content-type').split (';')[0].trim ();
	return new DOMParser().parseFromString(await res.text(), type);
}) ();

const getTemplate = async id => {
	const templates = await templatesPromise;
	return templates.getElementById (id);
};

const navigation = [location.href];

const navigate = async (uri, formData) => {
	if (new URL (uri, location.href).host !== location.host) {
		throw new TypeError ('Navigation target must be on the same origin');
	}
	
	let method, fetchOptions;
	if (formData instanceof FormData) {
		method = 'POST';
		fetchOptions = {method, body: formData, credentials: 'same-origin'};
	} else {
		method = 'GET';
		fetchOptions = {credentials: 'same-origin'};
	}
	
	const res = await fetch (uri, fetchOptions);
	
	const type = res.headers.get ('content-type').split (';')[0].trim ();
	const doc = new DOMParser().parseFromString(await res.text(), type);
	console.log ('fetched document:', doc);
	
	const newPage = doc.getElementsByTagName ('josocon-page')[0];
	const page = document.getElementsByTagName ('josocon-page')[0];
	if (!newPage) {
		console.error ('invalid document');
		return;
	}
	if (!page) {
		console.error ('not supported');
		return;
	}
	
	page.innerText = '';
	
	[... newPage.childNodes]
	.map (node => document.adoptNode (node))
	.forEach (node => page.appendChild (node));
	
	document.title = doc.title;
	
	if ('GET' === method) {
		const prev = navigation[navigation.length - 1];
		if (prev !== res.url) {
			navigation.push (res.url);
		}
	}
	history.replaceState ({}, "", res.url);
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
		this.load ()
		.then (a => console.log ('connected:', a))
		.catch (e => console.error (e));
	}
});

customElements.define ('josocon-markdown', class extends HTMLElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'open' });
		shadowRoots.set (this, shadowRoot);
	}
	
	connectedCallback () {
		const shadowRoot = shadowRoots.get (this);
		const html = '<div>' + md.render(this.textContent) + '</div>';
		const node = new DOMParser ().parseFromString (html, 'text/html').body.children[0];
		const link = document.createElement ('link');
		link.rel = 'stylesheet';
		link.href = '/resources/common.css';
		shadowRoot.appendChild (link);
		shadowRoot.appendChild (document.adoptNode (node));
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

