# Contao SVG Sprite Bundle

This bundle allows you to manage and use SVG sprites directly within
your Contao theme.

## Features

-   Upload and/or select an SVG sprite within your theme
-   Works with the legacy page layout and the Twig page layout (Contao 5.6+)
-   Reference individual `<symbol>` elements via insert tag or Twig function
-   Optional width, height, accessible label and extra CSS classes
-   Sprite inline in the page or referenced as a cacheable file
-   CSP friendly (no inline styles)
-   (Planned) Visual icon picker for selecting symbols
-   (Planned) Dynamic SVG sprite generation via Contao (instead of
    loading from the file system)

## Requirements

-   Contao 5.3+ or 6.x

## Installation

```
composer require plakart/contao-svg-sprite-bundle
```

Afterwards run the database migration (`contao:migrate` or the Contao Manager).

## Configuration

In the theme settings select the sprite file and how it is embedded:

-   **Inline in the HTML** (default): the sprite is added once at the end of
    the `<body>` of every page, wrapped in `<div hidden>`.
-   **Reference as file**: icons point to the file directly
    (`/files/…/icons.svg?v=<mtime>#ICON_ID`), so the browser can cache it.
    The cache busting parameter is the modification time of the file. The
    folder must be public (`.public` / "Make public" in the file manager).
    Recommended for large icon sets.

## Usage

### Insert Tag

    {{symbol::ICON_ID}}

Optional parameters:

    {{symbol::ICON_ID?width=32&height=32}}
    {{symbol::ICON_ID?label=Phone}}
    {{symbol::ICON_ID?class=my-class another-class}}

### Twig function

```twig
{{ symbol('arrow') }}
{{ symbol('phone', {width: 20, height: 20, label: 'Phone', class: 'contact__icon'}) }}
```

The insert tag and the Twig function share the same renderer and produce
identical output.

### HTML Output

Decorative icon (default):

```html
<svg class="sprite-svg-icon icon--lucide-archive-x" aria-hidden="true" focusable="false">
    <use href="#lucide-archive-x"/>
</svg>
```

Icon with meaning (`label` set, e.g. an icon as the only content of a link):

```html
<svg class="sprite-svg-icon icon--phone" role="img" aria-label="Phone">
    <use href="#phone"/>
</svg>
```

- General icon class `sprite-svg-icon`
- Specific class per icon `icon--ICON_ID`
- Additional classes from the `class` parameter

### Example SVG Sprite

The bundle hides the sprite itself, so no `style="display:none"` is needed
(which would violate a strict Content Security Policy). An XML declaration
in the file is removed automatically.

Requirements for the symbols in the sprite:

-   Use `currentColor` for `stroke`/`fill`, so the color comes from CSS.
    Fixed colors from design tool exports (e.g. `stroke="#D42A20"`) cannot
    be changed via CSS.
-   IDs must be unique within the whole sprite. Design tool exports often
    contain the same `clipPath` ID in every icon (`<clipPath id="clip1">`).
    Remove clip paths that only clip to the icon area.
-   Avoid `url(#…)` references (gradients, clip paths, masks): they do not
    work inside the hidden sprite container in some browsers. Plain
    `<symbol>` elements used via `<use>` work fine.

``` svg
<svg xmlns="http://www.w3.org/2000/svg">
    <symbol viewBox="0 0 24 24" id="test-icon">
        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 0 0-4 8"></path>
        </g>
    </symbol>

    <symbol viewBox="0 0 24 24" id="lucide-archive-x">
        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
            <rect width="20" height="5" x="2" y="3" rx="1"></rect>
            <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M9.5 17l5-5m-5 0l5 5"></path>
        </g>
    </symbol>
</svg>
```

## Roadmap

-   [ ] Visual icon picker for selecting symbols
-   [ ] Dynamic SVG sprite generation on page load (instead of
    file-based loading)
