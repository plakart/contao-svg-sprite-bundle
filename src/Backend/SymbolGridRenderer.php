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

namespace Plakart\ContaoSvgSpriteBundle\Backend;

use Plakart\ContaoSvgSpriteBundle\Sprite\SymbolLibrary;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Renders the symbol grid for the overview in the theme settings and for the
 * svgSymbol widget.
 */
#[Autoconfigure(public: true)]
class SymbolGridRenderer
{
    public function __construct(private readonly SymbolLibrary $symbolLibrary)
    {
    }

    /**
     * Read-only overview, a click copies the insert tag.
     */
    public function renderOverview(int $themeId): string
    {
        $this->addAssets();

        $symbols = $this->symbolLibrary->getSymbols($themeId);

        if (!$symbols) {
            return '<p class="tl_info">'.$this->e($this->trans('svgSpriteEmpty')).'</p>';
        }

        $items = '';

        foreach ($symbols as $symbol) {
            // Encode the braces, so the insert tag is never replaced in the output
            $tag = '&#123;&#123;symbol::'.$this->e($symbol['id']).'&#125;&#125;';

            $items .= \sprintf(
                '<li class="svg-sprite__item" data-svg-sprite-item data-id="%1$s"><button type="button" class="svg-sprite__tile" data-svg-sprite-copy="%2$s" title="%3$s"><img src="%4$s" alt="" width="32" height="32" loading="lazy"><span class="svg-sprite__id">%1$s</span></button></li>',
                $this->e($symbol['id']),
                $tag,
                $this->e(\sprintf($this->trans('svgSpriteCopy'), '{{symbol::'.$symbol['id'].'}}')),
                $symbol['preview'],
            );
        }

        return \sprintf(
            '<div class="svg-sprite" data-svg-sprite data-copied="%s">%s<ul class="svg-sprite__grid">%s</ul><p class="svg-sprite__status" role="status" aria-live="polite"></p></div>',
            $this->e($this->trans('svgSpriteCopied')),
            $this->renderSearch(null, \count($symbols)),
            $items,
        );
    }

    /**
     * Selectable grid (radio buttons) for the svgSymbol widget.
     *
     * @param array<int, array{name: string, symbols: list<array{id: string, preview: string}>}> $groups
     */
    public function renderPicker(string $name, string $id, string $value, array $groups, bool $mandatory = false): string
    {
        $this->addAssets();

        if (!$groups) {
            return '<p class="tl_info">'.$this->e($this->trans('svgSpriteNoSprite')).'</p>';
        }

        $count = 0;
        $checked = false;
        $index = 0;
        $html = '';

        // "No icon" option, always visible
        if (!$mandatory) {
            $html .= \sprintf(
                '<ul class="svg-sprite__grid"><li class="svg-sprite__item"><input type="radio" class="svg-sprite__radio" name="%1$s" id="opt_%2$s_none" value=""%3$s><label class="svg-sprite__tile svg-sprite__tile--none" for="opt_%2$s_none"><span class="svg-sprite__none" aria-hidden="true">∅</span><span class="svg-sprite__id">%4$s</span></label></li></ul>',
                $this->e($name),
                $this->e($id),
                '' === $value ? ' checked' : '',
                $this->e($this->trans('svgSpriteNone')),
            );
        }

        foreach ($groups as $group) {
            $items = '';

            foreach ($group['symbols'] as $symbol) {
                ++$count;
                $optionId = 'opt_'.$id.'_'.$index++;

                $items .= \sprintf(
                    '<li class="svg-sprite__item" data-svg-sprite-item data-id="%1$s"><input type="radio" class="svg-sprite__radio" name="%2$s" id="%3$s" value="%1$s"%4$s><label class="svg-sprite__tile" for="%3$s"><img src="%5$s" alt="" width="32" height="32" loading="lazy"><span class="svg-sprite__id">%1$s</span></label></li>',
                    $this->e($symbol['id']),
                    $this->e($name),
                    $this->e($optionId),
                    !$checked && ($checked = $value === $symbol['id']) ? ' checked' : '',
                    $symbol['preview'],
                );
            }

            $heading = \count($groups) > 1 ? '<h4 class="svg-sprite__theme">'.$this->e($group['name']).'</h4>' : '';
            $html .= \sprintf('<div data-svg-sprite-group>%s<ul class="svg-sprite__grid">%s</ul></div>', $heading, $items ?: '');
        }

        return \sprintf(
            '<div class="svg-sprite svg-sprite--picker" data-svg-sprite>%s%s</div>',
            $this->renderSearch('ctrl_'.$id, $count),
            $html,
        );
    }

    private function renderSearch(string|null $id, int $count): string
    {
        return \sprintf(
            '<div class="svg-sprite__toolbar"><input type="search" class="tl_text svg-sprite__search"%1$s data-svg-sprite-search placeholder="%2$s" aria-label="%2$s" autocomplete="off"><span class="svg-sprite__count">%3$s</span></div>',
            $id ? ' id="'.$this->e($id).'"' : '',
            $this->e($this->trans('svgSpriteSearch')),
            $this->e(\sprintf($this->trans('svgSpriteCount'), $count)),
        );
    }

    private function addAssets(): void
    {
        $GLOBALS['TL_CSS']['svg-sprite'] = 'bundles/contaosvgsprite/backend.css';
        $GLOBALS['TL_JAVASCRIPT']['svg-sprite'] = 'bundles/contaosvgsprite/backend.js';
    }

    private function trans(string $key): string
    {
        return (string) ($GLOBALS['TL_LANG']['MSC'][$key] ?? $key);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }
}
