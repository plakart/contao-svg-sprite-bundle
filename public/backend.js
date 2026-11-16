/*
 * This file is part of ContaoSvgSpriteBundle.
 *
 * Symbol overview and svgSymbol widget: search filter and copy to clipboard.
 * Uses event delegation, because the Contao backend replaces the page via Turbo.
 */
(() => {
    if (window.svgSpriteBackend) {
        return;
    }

    window.svgSpriteBackend = true;

    const filter = (input) => {
        const root = input.closest('[data-svg-sprite]');
        const query = input.value.trim().toLowerCase();

        root.querySelectorAll('[data-svg-sprite-item]').forEach((item) => {
            item.hidden = query !== '' && !item.dataset.id.toLowerCase().includes(query);
        });

        root.querySelectorAll('[data-svg-sprite-group]').forEach((group) => {
            group.hidden = !group.querySelector('[data-svg-sprite-item]:not([hidden])');
        });
    };

    const copy = async (text) => {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            // Fallback for insecure contexts (http) or denied permission
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.append(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
        }
    };

    document.addEventListener('input', (event) => {
        if (event.target.matches?.('[data-svg-sprite-search]')) {
            filter(event.target);
        }
    });

    // Enter in the search field must not submit the form
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.matches?.('[data-svg-sprite-search]')) {
            event.preventDefault();
        }
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest?.('[data-svg-sprite-copy]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const text = button.dataset.svgSpriteCopy;
        const root = button.closest('[data-svg-sprite]');
        const status = root?.querySelector('.svg-sprite__status');

        await copy(text);

        root?.querySelectorAll('.is-copied').forEach((el) => el.classList.remove('is-copied'));
        button.classList.add('is-copied');

        if (status) {
            status.textContent = `${root.dataset.copied} ${text}`;
        }

        clearTimeout(button.svgSpriteTimeout);
        button.svgSpriteTimeout = setTimeout(() => button.classList.remove('is-copied'), 1500);
    });
})();
