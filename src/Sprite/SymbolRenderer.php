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

/**
 * Renders the <svg><use> markup for a sprite symbol. Shared by the insert tag
 * and the Twig function, so both produce identical output.
 */
class SymbolRenderer
{
    public function __construct(private readonly SpriteLocator $spriteLocator)
    {
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

        $href = ($this->spriteLocator->getExternalUrlForCurrentPage() ?? '').'#'.$id;

        return \sprintf('<svg%s><use href="%s"/></svg>', $this->renderAttributes($attributes), $this->escape($href));
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
