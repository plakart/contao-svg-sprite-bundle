# Contao SVG Sprite Bundle

This bundle allows you to manage and use SVG sprites directly within
your Contao theme.

## Features

-   Upload and/or select an SVG sprite within your theme\
-   Reference individual `<symbol>` elements via insert tags\
-   Optional width and height parameters for flexible rendering\
-   (Planned) Visual icon picker for selecting symbols\
-   (Planned) Dynamic SVG sprite generation via Contao (instead of
    loading from the file system)

## Usage

### Insert Tag

Use the following insert tag to render an icon:

    {{symbol::ICON_ID}}

You can optionally define width and height:

    {{symbol::ICON_ID?width=32&height=32}}

### Example SVG Sprite

``` svg
<svg xmlns="http://www.w3.org/2000/svg" style="display:none;">
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

### HTML Output

```html
<svg class="sprite-svg-icon icon--lucide-archive-x">
    <use href="#lucide-archive-x"></use>
</svg>
```
- General icon class `sprite-svg-icon`
- Specific class per icon `icon--ICON_ID`

## Roadmap

-   [ ] Visual icon picker for selecting symbols\
-   [ ] Dynamic SVG sprite generation on page load (instead of
    file-based loading)
