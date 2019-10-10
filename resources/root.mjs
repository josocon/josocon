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

customElements.define ('josocon-page', class extends HTMLBodyElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'closed' });
		shadowRoots.set (this, shadowRoot);
	}
	
	async load () {
		const root = shadowRoots.get (this);
		if (root.childNodes.length) return true;
		
		const template = await getTemplate ('template-page');
		const content = document.importNode (template.content, true);
		root.appendChild (content);
		return true;
	}
	
	connectedCallback () {
		this.load ().then (a => console.log ('connected:', a)).catch (e => console.error (e));
	}
}, {extends: 'body'});

customElements.define ('josocon-markdown', class extends HTMLElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'closed' });
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
