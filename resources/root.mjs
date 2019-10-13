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

const navigate = async uri => {
	if (new URL (uri, location.href).host !== location.host) {
		throw new TypeError ('Navigation target must be on the same origin');
	}
	
	const res = await fetch (uri);
	const type = res.headers.get ('content-type').split (';')[0].trim ();
	const doc = new DOMParser().parseFromString(await res.text(), type);
	document.body.innerText = '';
	console.log ('fetched document:', doc);
	
	[... doc.body.childNodes]
	.map (node => document.adoptNode (node))
	.forEach (node => {
		document.body.appendChild (node);
		console.log ('appendChild:', node);
	});
	document.title = doc.title;
	history.replaceState ({}, "", uri);
};

customElements.define ('josocon-page', class extends HTMLBodyElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'open' });
		shadowRoots.set (this, shadowRoot);
		this.classList.add ('removed');
	}
	
	async load () {
		const root = shadowRoots.get (this);
		if (root.childNodes.length) return true;
		
		const template = await getTemplate ('template-page');
		const content = document.importNode (template.content, true);
		root.appendChild (content);
		this.classList.remove ('removed');
		return true;
	}
	
	connectedCallback () {
		this.load ()
		.then (a => console.log ('connected:', a))
		.catch (e => console.error (e));
	}
}, {extends: 'body'});

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

document.addEventListener ('load', e => {
	console.log (decodeURIComponent ('%0a%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%0a%0a%e6%9d%b1%e5%a4%a7%e5%a5%b3%e8%a3%85%e5%ad%90%e3%82%b3%e3%83%b3%e3%83%86%e3%82%b9%e3%83%88%e5%ae%9f%e8%a1%8c%e5%a7%94%e5%93%a1%e4%bc%9a%32%30%31%37%2d%32%30%31%38%0a%57%65%62%e3%83%87%e3%82%b6%e3%82%a4%e3%83%b3%e3%82%a2%e3%82%b7%e3%82%b9%e3%82%bf%e3%83%b3%e3%83%88%e5%8b%9f%e9%9b%86%ef%bc%81%0a%e5%88%9d%e5%bf%83%e8%80%85%e5%8f%af%e3%83%bb%e3%83%97%e3%83%ad%e3%82%b0%e3%83%a9%e3%83%9f%e3%83%b3%e3%82%b0%e7%9f%a5%e8%ad%98%e4%b8%8d%e5%95%8f%e3%83%bb%e3%82%b0%e3%83%a9%e3%83%95%e3%82%a3%e3%83%83%e3%82%af%e3%83%87%e3%82%b6%e3%82%a4%e3%83%b3%e3%82%84%e3%82%bf%e3%82%a4%e3%83%9d%e3%82%b0%e3%83%a9%e3%83%95%e3%82%a3%e3%81%ab%e8%88%88%e5%91%b3%e3%81%8c%e3%81%82%e3%82%8b%e4%ba%ba%e6%ad%93%e8%bf%8e%0a%e9%80%a3%e7%b5%a1%e5%85%88%20%54%77%69%74%74%65%72%3a%20%40%5f%75%74%73%32%0a%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%5f%0a'));
});

window.addEventListener ('DOMContentLoaded', e => {
	document.documentElement.classList.remove ('removed');
});

document.addEventListener ('click', ev => {
	const composedPath = ev.composedPath ();
	for (let target of composedPath) {
		console.log (target);
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

