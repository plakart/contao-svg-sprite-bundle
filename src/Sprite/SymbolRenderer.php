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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Renders the <svg><use> markup for a sprite symbol. Shared by the insert tag
 * and the Twig function, so both produce identical output.
 *
 * In the back end (e.g. previews of content elements) there is no sprite in the
 * document, so the symbol content is inlined instead of referenced.
 */
class SymbolRenderer
{
    public function __construct(
        private readonly SpriteLocator $spriteLocator,
        private readonly SymbolLibrary $symbolLibrary,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param array{width?: int|string|null, height?: int|string|null, label?: string|null, class?: string|null} $options
     */
    public function render(string $id, array $options = []): string
    {
        $id = trim($id);

        if ('' === $id) {
            return '';
        }

        $classes = ['sprite-svg-icon', 'icon--'.$id];

        if ('' !== ($extra = trim((string) ($options['class'] ?? '')))) {
            array_push($classes, ...preg_split('/\s+/', $extra));
        }

        $attributes = ['class' => implode(' ', array_unique($classes))];

        foreach (['width', 'height'] as $name) {
            if (isset($options[$name]) && '' !== (string) $options[$name]) {
                $attributes[$name] = (string) $options[$name];
            }
        }

        $label = trim((string) ($options['label'] ?? ''));

        if ('' !== $label) {
            $attributes['role'] = 'img';
            $attributes['aria-label'] = $label;
        } else {
            $attributes['aria-hidden'] = 'true';
            $attributes['focusable'] = 'false';
        }

        if ($this->isBackendRequest() && ($symbol = $this->symbolLibrary->findSymbol($id))) {
            $attributes['viewBox'] = $symbol['viewBox'];

            // The front end CSS is not loaded in the back end, so prevent the
            // icon from filling the whole preview
            if (!isset($attributes['width']) && !isset($attributes['height'])) {
                $attributes['width'] = $attributes['height'] = '1em';
            }

            // The markup is sanitized by the SymbolLibrary
            return \sprintf('<svg%s>%s</svg>', $this->renderAttributes($attributes), $symbol['markup']);
        }

        $href = ($this->spriteLocator->getExternalUrlForCurrentPage() ?? '').'#'.$id;

        return \sprintf('<svg%s><use href="%s"/></svg>', $this->renderAttributes($attributes), $this->escape($href));
    }

    private function isBackendRequest(): bool
    {
        $request = $this->requestStack->getMainRequest();

        return null !== $request && $this->scopeMatcher->isBackendRequest($request);
    }

    /**
     * @param array<string, string> $attributes
     */
    private function renderAttributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $name => $value) {
            $html .= \sprintf(' %s="%s"', $name, $this->escape($value));
        }

        return $html;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }
}
