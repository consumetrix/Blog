/*
 * Rich text fields in the page builder (TipTap). Only the formatting the server's
 * sanitiser keeps is offered: paragraphs, H2–H4, bold, italic, strike, lists, quotes,
 * horizontal rules, links, and (with `align`) text alignment.
 */

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';

// `group: true` starts a new group of buttons (styled as a gap where the toolbar wants one).
const BUTTONS = [
    { label: 'Paragraph', text: '¶', run: (c) => c.setParagraph(), active: (e) => e.isActive('paragraph') },
    { label: 'Heading', text: 'H2', run: (c) => c.toggleHeading({ level: 2 }), active: (e) => e.isActive('heading', { level: 2 }) },
    { label: 'Subheading', text: 'H3', run: (c) => c.toggleHeading({ level: 3 }), active: (e) => e.isActive('heading', { level: 3 }) },
    { label: 'Bold', text: 'B', run: (c) => c.toggleBold(), active: (e) => e.isActive('bold'), style: 'font-weight:700', group: true },
    { label: 'Italic', text: 'I', run: (c) => c.toggleItalic(), active: (e) => e.isActive('italic'), style: 'font-style:italic' },
    { label: 'Bulleted list', text: '•', run: (c) => c.toggleBulletList(), active: (e) => e.isActive('bulletList'), group: true },
    { label: 'Numbered list', text: '1.', run: (c) => c.toggleOrderedList(), active: (e) => e.isActive('orderedList') },
    { label: 'Quote', text: '“', run: (c) => c.toggleBlockquote(), active: (e) => e.isActive('blockquote') },
    { label: 'Link', text: 'Link', link: true, active: (e) => e.isActive('link'), group: true },
    { label: 'Clear formatting', text: 'Clear', run: (c) => c.unsetAllMarks().clearNodes(), active: () => false },
];

// Alignment icons: four lines, set left, centred, right or full width.
const lines = (rows) => `<svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">${rows.map(([x, w], i) => `<rect x="${x}" y="${2 + i * 4}" width="${w}" height="1.6" rx=".8" fill="currentColor"/>`).join('')}</svg>`;

const ALIGNMENT = [
    { label: 'Align left', icon: lines([[1, 14], [1, 9], [1, 14], [1, 9]]), run: (c) => c.setTextAlign('left'), active: (e) => e.isActive({ textAlign: 'left' }), group: true },
    { label: 'Centre', icon: lines([[1, 14], [3.5, 9], [1, 14], [3.5, 9]]), run: (c) => c.setTextAlign('center'), active: (e) => e.isActive({ textAlign: 'center' }) },
    { label: 'Align right', icon: lines([[1, 14], [6, 9], [1, 14], [6, 9]]), run: (c) => c.setTextAlign('right'), active: (e) => e.isActive({ textAlign: 'right' }) },
    { label: 'Justify', icon: lines([[1, 14], [1, 14], [1, 14], [1, 14]]), run: (c) => c.setTextAlign('justify'), active: (e) => e.isActive({ textAlign: 'justify' }) },
];

const HISTORY = [
    { label: 'Undo', text: '↶', run: (c) => c.undo(), active: () => false, group: true },
    { label: 'Redo', text: '↷', run: (c) => c.redo(), active: () => false },
];

/**
 * @param {{ history?: boolean, align?: boolean }} options  history: Undo and Redo buttons;
 *   align: left, centre, right and justify for paragraphs and headings.
 */
export function createRichText(holder, html, onChange, options = {}) {
    const toolbar = document.createElement('div');
    toolbar.className = 'ed-rt__bar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'Text formatting');

    const surface = document.createElement('div');
    surface.className = 'ed-rt__surface';
    holder.replaceChildren(toolbar, surface);

    const editor = new Editor({
        element: surface,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
                code: false,
                codeBlock: false,
                underline: false,
                link: {
                    openOnClick: false,
                    autolink: true,
                    protocols: ['mailto', 'tel'],
                    HTMLAttributes: { rel: 'noopener', target: null },
                },
            }),
            ...(options.align ? [TextAlign.configure({ types: ['heading', 'paragraph'], alignments: ['left', 'center', 'right', 'justify'] })] : []),
        ],
        content: html || '<p></p>',
        editorProps: { attributes: { class: 'ed-rt__content prose', 'aria-multiline': 'true', role: 'textbox' } },
        onUpdate: ({ editor: e }) => onChange(e.isEmpty ? '' : e.getHTML()),
        onSelectionUpdate: () => sync(),
        onTransaction: () => sync(),
    });

    const buttons = [...BUTTONS, ...(options.align ? ALIGNMENT : []), ...(options.history ? HISTORY : [])].map((def) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = def.group ? 'ed-rt__btn ed-rt__btn--group' : 'ed-rt__btn';
        button.title = def.label;
        button.setAttribute('aria-label', def.label);
        if (def.icon) button.innerHTML = def.icon;
        else button.textContent = def.text;
        if (def.style) button.setAttribute('style', def.style);
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            if (def.link) {
                const current = editor.getAttributes('link').href || '';
                const url = window.prompt('Link address (/page, #section, https://…, mailto:…). Leave empty to remove the link.', current);
                if (url === null) return;
                const chain = editor.chain().focus().extendMarkRange('link');
                if (url.trim() === '') chain.unsetLink().run();
                else chain.setLink({ href: url.trim() }).run();
                return;
            }
            def.run(editor.chain().focus()).run();
        });
        toolbar.append(button);
        return [button, def];
    });

    function sync() {
        buttons.forEach(([button, def]) => button.setAttribute('aria-pressed', def.active(editor) ? 'true' : 'false'));
    }
    sync();

    return {
        element: holder,
        destroy: () => editor.destroy(),
    };
}
