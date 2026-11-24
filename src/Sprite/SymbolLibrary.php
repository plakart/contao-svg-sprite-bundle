<?php

declare(strict_types=1);

/*
 * This file is part of ContaoSvgSpriteBundle.
 *
 * (c) plakart GmbH & Co. KG (https://plakart.de)
 * author Jannik Nölke (https://jaynoe.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plakart\ContaoSvgSpriteBundle\Sprite;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Reads the <symbol> elements of a theme's sprite and builds a standalone
 * preview image for each of them (used by the backend overview and widget).
 */
#[Autoconfigure(public: true)]
class SymbolLibrary implements ResetInterface
{
    private const SVG_NS = 'http://www.w3.org/2000/svg';

    /**
     * @var array<int, list<array{id: string, viewBox: string, markup: string, preview: string}>>
     */
    private array $symbolsByTheme = [];

    public function __construct(private readonly SpriteLocator $spriteLocator)
    {
    }

    public function reset(): void
    {
        $this->symbolsByTheme = [];
    }

    /**
     * @return list<array{id: string, viewBox: string, markup: string, preview: string}>
     */
    public function getSymbols(int $themeId): array
    {
        if (isset($this->symbolsByTheme[$themeId])) {
            return $this->symbolsByTheme[$themeId];
        }

        $path = $this->spriteLocator->getSpritePath($themeId);

        if (null === $path || false === ($content = file_get_contents($path))) {
            return $this->symbolsByTheme[$themeId] = [];
        }

        return $this->symbolsByTheme[$themeId] = $this->parse($content);
    }

    /**
     * Returns the symbols grouped by theme. Without a theme ID, all themes
     * with a sprite are returned.
     *
     * @return array<int, array{name: string, symbols: list<array{id: string, viewBox: string, markup: string, preview: string}>}>
     */
    public function getSymbolsByTheme(int|null $themeId = null): array
    {
        $groups = [];

        foreach ($this->spriteLocator->getThemesWithSprite() as $id => $name) {
            if (null !== $themeId && $themeId !== $id) {
                continue;
            }

            $groups[$id] = ['name' => $name, 'symbols' => $this->getSymbols($id)];
        }

        return $groups;
    }

    /**
     * Finds a symbol by ID in the sprites of all themes (first match wins).
     * Used where there is no current page, e.g. previews in the back end.
     *
     * @return array{id: string, viewBox: string, markup: string, preview: string}|null
     */
    public function findSymbol(string $id): array|null
    {
        foreach ($this->getSymbolsByTheme() as $group) {
            foreach ($group['symbols'] as $symbol) {
                if ($symbol['id'] === $id) {
                    return $symbol;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, viewBox: string, markup: string, preview: string}>
     */
    private function parse(string $content): array
    {
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);

        // No LIBXML_NOENT: entities are not substituted (no XXE)
        $loaded = $document->loadXML($content, LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return [];
        }

        $this->sanitize($document);

        // Shared definitions outside of symbols (e.g. gradients in <defs>)
        $defs = '';

        foreach ($document->documentElement?->childNodes ?? [] as $node) {
            if ($node instanceof \DOMElement && 'defs' === $node->localName && !$node->getElementsByTagNameNS('*', 'symbol')->length) {
                $defs .= $document->saveXML($node);
            }
        }

        $symbols = [];
        $seen = [];

        foreach ($document->getElementsByTagNameNS('*', 'symbol') as $symbol) {
            $id = trim($symbol->getAttribute('id'));

            if ('' === $id || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $inner = '';

            foreach ($symbol->childNodes as $child) {
                $inner .= $document->saveXML($child);
            }

            $viewBox = $symbol->getAttribute('viewBox') ?: '0 0 24 24';

            // A standalone SVG document per symbol, rendered as <img>: no ID
            // conflicts between sprites and no script execution in the backend.
            $svg = \sprintf(
                '<svg xmlns="%s" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="%s" width="48" height="48" color="#1f2937">%s%s</svg>',
                self::SVG_NS,
                htmlspecialchars($viewBox, ENT_QUOTES | ENT_XML1),
                $defs,
                $inner,
            );

            $symbols[] = [
                'id' => $id,
                'viewBox' => $viewBox,
                'markup' => $inner,
                'preview' => 'data:image/svg+xml;base64,'.base64_encode($svg),
            ];
        }

        return $symbols;
    }

    /**
     * The symbol markup is inlined into back end pages, so remove everything
     * that could execute code, change the page styles or load resources.
     */
    private function sanitize(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        $elements = $xpath->query('//*[contains("|script|style|foreignObject|iframe|object|embed|image|handler|listener|", concat("|", local-name(), "|"))]');

        foreach (iterator_to_array($elements ?: []) as $element) {
            $element->parentNode?->removeChild($element);
        }

        foreach (iterator_to_array($xpath->query('//@*') ?: []) as $attribute) {
            $name = strtolower($attribute->localName);
            $value = trim($attribute->value);

            $remove = str_starts_with($name, 'on')
                || ('href' === $name && !str_starts_with($value, '#'))
                || preg_match('/url\(\s*[\'"]?\s*(?!#)/i', $value);

            if ($remove) {
                $attribute->ownerElement?->removeAttributeNode($attribute);
            }
        }
    }
}
