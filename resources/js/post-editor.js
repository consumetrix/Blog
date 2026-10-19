/*
 * Dashboard → Posts: the article text is written in the same rich text editor as the page
 * builder (resources/js/editor/richtext.js). It fills the hidden form field it replaces and
 * keeps the word count and reading time up to date.
 */

import { createRichText } from './editor/richtext';

function words(html) {
    const text = new DOMParser().parseFromString(html, 'text/html').body.textContent.trim();
    return text ? text.split(/\s+/).length : 0;
}

document.querySelectorAll('textarea[data-richtext]').forEach((field) => {
    const count = document.querySelector(field.dataset.richtextCount);
    const update = (html) => {
        if (!count) return;
        const n = words(html);
        count.textContent = `${n} ${n === 1 ? 'word' : 'words'} · ${Math.max(1, Math.round(n / 220))} min read`;
    };

    const holder = document.createElement('div');
    holder.className = 'dash-rt';
    field.after(holder);
    field.hidden = true;

    createRichText(holder, field.value, (html) => {
        field.value = html;
        update(html);
    }, { history: true, align: true });

    const surface = holder.querySelector('[role="textbox"]');
    surface.setAttribute('aria-label', 'Article text');
    // The field's label now focuses the editor.
    document.querySelector(`label[for="${field.id}"]`)?.addEventListener('click', (event) => {
        event.preventDefault();
        surface.focus();
    });

    update(field.value);
});
