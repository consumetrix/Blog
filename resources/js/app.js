/*
 * Progressive enhancement only: every page works without this file.
 * Hooks are data attributes rendered by the Blade components (see docs/BUILD-HANDOFF.md).
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function initHeader() {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    const progress = header.querySelector('[data-scroll-progress]');
    let queued = false;

    const update = () => {
        queued = false;
        header.classList.toggle('is-scrolled', window.scrollY > 8);
        if (progress) {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.transform = `scaleX(${max > 0 ? Math.min(1, window.scrollY / max) : 0})`;
        }
    };
    update();
    window.addEventListener('scroll', () => {
        if (!queued) {
            queued = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });
    window.addEventListener('resize', update, { passive: true });
}

function initMenu() {
    const menu = document.querySelector('[data-menu]');
    const opener = document.querySelector('[data-menu-open]');
    if (!menu || !opener) return;

    const closer = menu.querySelector('[data-menu-close]');
    let lastFocus = null;

    const focusables = () => [...menu.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);

    const onKeydown = (event) => {
        if (event.key === 'Escape') {
            close();
            return;
        }
        if (event.key !== 'Tab') return;

        const items = focusables();
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    function open() {
        lastFocus = document.activeElement;
        menu.hidden = false;
        document.body.classList.add('is-locked');
        opener.setAttribute('aria-expanded', 'true');
        requestAnimationFrame(() => {
            menu.classList.add('is-open');
            (closer || focusables()[0])?.focus();
        });
        document.addEventListener('keydown', onKeydown);
    }

    function close() {
        menu.classList.remove('is-open');
        document.body.classList.remove('is-locked');
        opener.setAttribute('aria-expanded', 'false');
        document.removeEventListener('keydown', onKeydown);

        const finish = () => {
            menu.hidden = true;
        };
        if (reduceMotion.matches) {
            finish();
        } else {
            menu.addEventListener('transitionend', finish, { once: true });
            setTimeout(finish, 400);
        }
        (lastFocus || opener).focus();
    }

    opener.addEventListener('click', open);
    closer?.addEventListener('click', close);

    // Close when the viewport grows past the mobile breakpoint so the page is never left locked.
    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches && !menu.hidden) close();
    });
}

function initReveal(root = document) {
    const items = root.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (!('IntersectionObserver' in window) || reduceMotion.matches) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    items.forEach((el) => observer.observe(el));
}

function initTabs(root = document) {
    root.querySelectorAll('[data-tabs]').forEach((container) => {
        const list = container.querySelector('[data-tabs-list]');
        if (!list) return;

        const tabs = [...list.querySelectorAll('[role="tab"][data-tab]')];
        const panels = new Map(
            [...container.querySelectorAll('[data-tab-panel]')].map((panel) => [panel.dataset.tabPanel, panel])
        );
        const vertical = list.getAttribute('aria-orientation') === 'vertical';

        panels.forEach((panel, key) => {
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('tabindex', '0');
            const tab = tabs.find((t) => t.dataset.tab === key);
            if (tab) panel.setAttribute('aria-labelledby', tab.id);
        });

        const select = (key, focus = false) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.tab === key;
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.tabIndex = active ? 0 : -1;
                if (active && focus) tab.focus();
            });
            panels.forEach((panel, panelKey) => {
                panel.hidden = panelKey !== key;
            });
        };

        list.addEventListener('click', (event) => {
            const tab = event.target.closest('[role="tab"]');
            if (tab) select(tab.dataset.tab);
        });

        list.addEventListener('keydown', (event) => {
            const index = tabs.indexOf(document.activeElement);
            if (index === -1) return;

            const prev = vertical ? 'ArrowUp' : 'ArrowLeft';
            const next = vertical ? 'ArrowDown' : 'ArrowRight';
            let target = null;

            if (event.key === prev || event.key === (vertical ? 'ArrowLeft' : 'ArrowUp')) target = (index - 1 + tabs.length) % tabs.length;
            else if (event.key === next || event.key === (vertical ? 'ArrowRight' : 'ArrowDown')) target = (index + 1) % tabs.length;
            else if (event.key === 'Home') target = 0;
            else if (event.key === 'End') target = tabs.length - 1;

            if (target === null) return;
            event.preventDefault();
            select(tabs[target].dataset.tab, true);
        });

        select(container.dataset.tabsDefault || tabs[0]?.dataset.tab);
    });
}

function initDiagnostic(root = document) {
    const form = root.querySelector('[data-diagnostic]');
    if (!form) return;

    const questions = [...form.querySelectorAll('[data-question]')];
    const segments = [...form.querySelectorAll('[data-progress]')];
    const count = form.querySelector('[data-diagnostic-count]');
    const back = form.querySelector('[data-diagnostic-back]');
    const submit = form.querySelector('[data-diagnostic-submit]');
    const answered = (q) => q.querySelector('input:checked') !== null;

    // Resume at the first unanswered question (answers survive "Adjust answers").
    let step = questions.findIndex((q) => !answered(q));
    if (step === -1) step = questions.length - 1;

    const show = (index, focus = false) => {
        step = index;
        questions.forEach((q, i) => {
            q.hidden = i !== index;
        });
        segments.forEach((s, i) => s.classList.toggle('is-done', i < index || (i === index && answered(questions[i]))));
        if (count) count.textContent = String(index + 1);
        if (back) back.hidden = index === 0;
        if (submit) submit.hidden = !questions.every(answered);
        if (focus) questions[index].querySelector('input:checked, input')?.focus();
    };

    const advance = (index) => {
        if (index < questions.length - 1) {
            setTimeout(() => show(index + 1, true), reduceMotion.matches ? 0 : 180);
        } else if (questions.every(answered)) {
            form.requestSubmit ? form.requestSubmit() : form.submit();
        } else {
            show(questions.findIndex((q) => !answered(q)), true);
        }
    };

    // Arrow keys change a radio selection too, so only pointer choices auto-advance;
    // keyboard users confirm with Enter.
    let pointerChoice = false;
    form.addEventListener('pointerdown', () => {
        pointerChoice = true;
    });

    form.addEventListener('change', (event) => {
        const question = event.target.closest('[data-question]');
        if (!question) return;
        if (pointerChoice) {
            pointerChoice = false;
            advance(questions.indexOf(question));
        } else {
            show(step);
        }
    });

    form.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.target.type !== 'radio') return;
        event.preventDefault();
        const question = event.target.closest('[data-question]');
        if (question && answered(question)) advance(questions.indexOf(question));
    });

    back?.addEventListener('click', () => show(Math.max(0, step - 1), true));

    show(step);
}

function initClock() {
    const clocks = document.querySelectorAll('[data-clock]');
    if (!clocks.length) return;

    let formatter;
    try {
        formatter = new Intl.DateTimeFormat('en-GB', { timeZone: 'Africa/Accra', hour: '2-digit', minute: '2-digit', hour12: false });
    } catch {
        return;
    }

    const tick = () => {
        const now = new Date();
        clocks.forEach((clock) => {
            clock.textContent = formatter.format(now);
            clock.setAttribute('datetime', now.toISOString());
        });
    };
    tick();
    setInterval(tick, 30_000);
}

function initErrorSummary() {
    document.querySelector('[data-error-summary]')?.focus();
}

function initAudit(root = document) {
    const audits = root.querySelectorAll('[data-audit]');
    audits.forEach((audit) => {
        const counter = audit.querySelector('[data-audit-counter]');
        const items = audit.querySelectorAll('[data-audit-item]');
        if (!counter || !items.length) return;

        const update = () => {
            const checked = [...items].filter((i) => i.checked).length;
            counter.textContent = `${checked} / ${items.length} Validated`;
            if (checked === items.length) {
                counter.style.background = 'rgba(43, 163, 160, 0.2)';
                counter.style.borderColor = 'rgba(43, 163, 160, 0.5)';
                counter.style.color = '#2BA3A0';
            } else {
                counter.style.background = 'rgba(199, 154, 69, 0.15)';
                counter.style.borderColor = 'rgba(199, 154, 69, 0.3)';
                counter.style.color = 'var(--gold-light)';
            }
        };

        items.forEach((item) => item.addEventListener('change', update));
        update();
    });
}

function initDashboard() {
    document.querySelectorAll('[data-dash-drop]').forEach((drop) => {
        const input = drop.querySelector('input[type="file"]');
        const preview = drop.querySelector('[data-drop-preview]');
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            drop.classList.add('has-file');
        });
        input.addEventListener('dragenter', () => drop.classList.add('is-over'));
        ['dragleave', 'drop'].forEach((type) => input.addEventListener(type, () => drop.classList.remove('is-over')));
    });

    // Media library: choosing or dropping files uploads them straight away.
    document.querySelectorAll('input[data-auto-submit]').forEach((input) => {
        const zone = input.closest('[data-upload-zone]');
        input.addEventListener('dragenter', () => zone?.classList.add('is-over'));
        ['dragleave', 'drop'].forEach((type) => input.addEventListener(type, () => zone?.classList.remove('is-over')));
        input.addEventListener('change', () => {
            if (!input.files?.length) return;
            zone?.classList.add('is-busy');
            input.form.requestSubmit();
        });
    });

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                const label = button.innerHTML;
                button.textContent = 'Copied';
                setTimeout(() => { button.innerHTML = label; }, 1600);
            } catch {
                window.prompt('Copy the address:', button.dataset.copy);
            }
        });
    });

    // Single-line fields that wrap: Enter is ignored and the height follows the text.
    document.querySelectorAll('textarea[data-autosize]').forEach((field) => {
        const fit = () => {
            field.style.height = 'auto';
            field.style.height = `${field.scrollHeight}px`;
        };
        field.rows = 1;
        field.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') event.preventDefault();
        });
        field.addEventListener('input', fit);
        window.addEventListener('resize', fit, { passive: true });
        fit();
    });

    document.querySelectorAll('[data-wordcount]').forEach((field) => {
        const out = document.querySelector(field.dataset.wordcount);
        if (!out) return;
        const update = () => {
            const words = field.value.trim() ? field.value.trim().split(/\s+/).length : 0;
            out.textContent = `${words} ${words === 1 ? 'word' : 'words'} · ${Math.max(1, Math.round(words / 220))} min read`;
        };
        field.addEventListener('input', update);
        update();
    });
}

// The page builder preview swaps sections in place and runs this on each new one.
window.Consumetrix = {
    init(root) {
        initTabs(root);
        initReveal(root);
        initDiagnostic(root);
        initAudit(root);
    },
};

if (window.top === window) {
    console.log('%cDeveloped by Benjamin Yamoah', 'font: 600 13px/1.6 system-ui, sans-serif; color: #c79a45;');
}

document.addEventListener('DOMContentLoaded', () => {
    initDashboard();
    initHeader();
    initMenu();
    initTabs();
    initReveal();
    initDiagnostic();
    initClock();
    initAudit();
    initErrorSummary();

    requestAnimationFrame(() => document.body.classList.add('is-loaded'));
});
