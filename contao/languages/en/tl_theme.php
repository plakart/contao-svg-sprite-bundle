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

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteFile'] = [
    'SVG sprite file',
    'Select/upload the SVG sprite file.'
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteMode'] = [
    'Sprite embedding',
    'Inline: the sprite is embedded in the HTML of every page. As file: icons reference the file, which the browser can cache. The file must be public.'
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteModes'] = [
    'inline'   => 'Inline in the HTML',
    'external' => 'Reference as file',
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteSymbols'] = [
    'Symbols in the sprite',
    'A click copies the insert tag to the clipboard.'
];
