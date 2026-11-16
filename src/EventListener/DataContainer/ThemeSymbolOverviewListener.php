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

namespace Plakart\ContaoSvgSpriteBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Plakart\ContaoSvgSpriteBundle\Backend\SymbolGridRenderer;
use Plakart\ContaoSvgSpriteBundle\Sprite\SpriteLocator;

/**
 * Shows all symbols of the sprite below the sprite field in the theme settings.
 */
class ThemeSymbolOverviewListener
{
    public function __construct(
        private readonly SpriteLocator $spriteLocator,
        private readonly SymbolGridRenderer $renderer,
    ) {
    }

    #[AsCallback(table: 'tl_theme', target: 'fields.svgSpriteSymbols.input_field')]
    public function __invoke(DataContainer $dc): string
    {
        $themeId = (int) $dc->id;

        if (!$themeId || null === $this->spriteLocator->getSpritePath($themeId)) {
            return '';
        }

        $label = $GLOBALS['TL_LANG']['tl_theme']['svgSpriteSymbols'] ?? ['Symbols', ''];

        return \sprintf(
            '<div class="widget clr"><h3>%s</h3>%s<p class="tl_help tl_tip">%s</p></div>',
            htmlspecialchars((string) $label[0], ENT_QUOTES | ENT_HTML5),
            $this->renderer->renderOverview($themeId),
            htmlspecialchars((string) ($label[1] ?? ''), ENT_QUOTES | ENT_HTML5),
        );
    }
}
