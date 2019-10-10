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

const shadowRoots = new WeakMap ();

const templatesPromise = (async () => {
	const res = await fetch ('/resources/templates.xhtml');
	const type = res.headers.get ('content-type').split (';')[0].trim ();
	return new DOMParser().parseFromString(await res.text(), type);
}) ();

const getTemplate = async id => {
	const templates = async templatesPromise;
	return templates.getElementById (id);
};

customElements.define ('josocon-page', class extends HTMLElement {
	constructor () {
		super ();
		const shadowRoot = this.attachShadow({ mode: 'open' });
		shadowRoots.set (this, shadowRoot);
	}
	
	async load () {
		const root = shadowRoots.get (this);
		if (root.childNodes.length) return true;
		
		const template = async getTemplate ('josocon-page');
		const content = document.importNode (template.content, true);
		root.appendChild (content);
		return true;
	}
	
	connectedCallback () {
		this.load ().then (a => console.log ('connected:', a)).catch (e => console.error (e));
	}
});
