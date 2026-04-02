# Contao SVG Sprite Bundle

- Im Theme eine SVG-Sprite hochladen und/oder auswählen (Beispiel unten)
- Via Insert-Tag die ID vom `<symbol>` auswählen (Beispiel unten)

## Insert-Tag Beispiel

`{{symbol::ICON_ID}}` oder mit Breite/Höhe Parameter: `{{symbol::ICON_ID?width=32&height=32}}`

## SVG-Sprite Beispiel

```svg
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display:none;">
    <symbol viewBox="0 0 24 24" id="test-icon"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"></path></g></symbol>
    <symbol viewBox="0 0 24 24" id="lucide-archive-x"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect width="20" height="5" x="2" y="3" rx="1"></rect><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M9.5 17l5-5m-5 0l5 5"></path></g></symbol>
</svg>
```

## Möglche Feautues
- Icons aus der Sprite-SVG können visuell über einen Picker ausgewählt werden #1
- SVG-Sprite wird nicht aus der Dateiverwaltung geladen, sondern über Contao erstellt (Dynamische Generierung beim PageLoad) #2