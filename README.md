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
-   Symbol overview with search and copy-to-clipboard in the theme settings
-   Icon picker input type (`svgSymbol`) for your own DCA fields
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

### Symbol overview

After selecting a sprite, the theme settings show all symbols of the sprite
with a preview and a search field. A click on a symbol copies its insert tag
(`{{symbol::ICON_ID}}`) to the clipboard, e.g. to paste it into the rich
text editor.

### Icon picker (input type `svgSymbol`)

The bundle is a toolkit: it provides the sprite, the insert tag, the Twig
function and an **icon picker input type**, but it does **not add any icon
field** on its own. Where an icon is needed (pages, content elements,
modules, …) and how it is rendered differs from project to project, so you
add the DCA field and the template output yourself.

The picker

-   shows the symbols of all themes with a sprite as a grid with preview and
    search, grouped by theme if there is more than one,
-   stores only the symbol ID (e.g. `phone`),
-   accepts only IDs that exist in a sprite,
-   offers a "No icon" option unless the field is `mandatory`.

Options (`eval`):

| Option           | Description                                              |
|------------------|----------------------------------------------------------|
| `svgSpriteTheme` | Theme ID: only offer the symbols of this theme's sprite. |
| `mandatory`      | An icon must be selected, no "No icon" option.           |
| `tl_class`       | As usual, `clr` is recommended (the grid is wide).       |

#### Example: an icon per page in the navigation

Editors should be able to pick an icon for each page, and the navigation
shows it in front of the page title. All files go into your project
(not into the bundle).

**1. Add the field to the page** – `contao/dca/tl_page.php`

```php
<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_page']['fields']['navIcon'] = [
    'exclude'   => true,
    'inputType' => 'svgSymbol',
    'eval'      => ['tl_class' => 'clr'],
    'sql'       => "varchar(128) NOT NULL default ''",
];

// New, collapsed legend "Icon" after the meta data, for the page types
// that appear in the navigation
PaletteManipulator::create()
    ->addLegend('icon_legend', 'meta_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('navIcon', 'icon_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page')
    ->applyToPalette('forward', 'tl_page')
    ->applyToPalette('redirect', 'tl_page')
;
```

**2. Add labels** – `contao/languages/de/tl_page.php` (and `en/` etc.)

```php
<?php

$GLOBALS['TL_LANG']['tl_page']['icon_legend'] = 'Icon';
$GLOBALS['TL_LANG']['tl_page']['navIcon'] = [
    'Icon in der Navigation',
    'Wird in der Navigation vor dem Seitentitel angezeigt.',
];
```

**3. Update the database**

```
vendor/bin/contao-console contao:migrate
```

(or the database update in the Contao Manager). Then edit a page, open the
legend "Icon" and pick an icon.

**4. Output the icon in the navigation template**

Navigation items contain all fields of the page, so the new field is
available as `item.navIcon`. Copy the core template into your `templates/`
folder and put the icon in front of the link text. There are two places,
one for the active item (`<strong>`) and one for the other items (`<a>`).

Contao 5.7+ / 6 (Twig), `templates/nav_default.html.twig`:

```twig
{# before #}
}}>{{ item.link }}</strong>
…
}}>{{ item.link }}</a>

{# after #}
}}>{% if item.navIcon|default %}{{ symbol(item.navIcon) }}{% endif %}{{ item.link }}</strong>
…
}}>{% if item.navIcon|default %}{{ symbol(item.navIcon) }}{% endif %}{{ item.link }}</a>
```

Contao 5.3 (PHP template), `templates/nav_default.html5` – use the insert
tag, it produces the same output:

```php
<?php if ($item['navIcon'] ?? ''): ?>{{symbol::<?= $item['navIcon'] ?>}}<?php endif; ?><?= $item['link'] ?>
```

To use the icon only in one navigation module, save the template under a
different name (e.g. `nav_icons.html.twig`) and select it as navigation
template in the module.

**5. Style it**

The icon gets the classes `sprite-svg-icon` and `icon--ID` and uses
`currentColor`, so it takes the color of the link:

```css
.mod_navigation .sprite-svg-icon {
    width: 1em;
    height: 1em;
    margin-right: .4em;
    vertical-align: -.125em;
}
```

**Result**

```html
<li class="active"><strong class="active" aria-current="page"><svg class="sprite-svg-icon icon--phone" aria-hidden="true" focusable="false"><use href="#phone"/></svg>Contact</strong></li>
```

The icon is decorative (`aria-hidden`), the page title stays the
accessible name of the link.

#### Other places

The same works for any DCA. Only the way to access the value in the
template differs:

-   Twig templates of content elements and front end modules (fragment
    controllers) get the record in `data`:
    `{% if data.icon %}{{ symbol(data.icon) }}{% endif %}`
-   In the Twig page layout (Contao 5.6+) the current page is available as
    `page`: `{% if page.navIcon %}{{ symbol(page.navIcon) }}{% endif %}`
-   In PHP templates use the insert tag:
    `{{symbol::<?= $this->icon ?>}}`

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

-   [x] Visual icon picker for selecting symbols
-   [ ] Dynamic SVG sprite generation on page load (instead of
    file-based loading)
