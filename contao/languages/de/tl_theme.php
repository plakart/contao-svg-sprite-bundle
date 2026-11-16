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
    'SVG Sprite Datei',
    'Auswählen/Hochladen der SVG-Sprite Datei.'
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteMode'] = [
    'Einbindung des Sprites',
    'Inline: Der Sprite steht auf jeder Seite im HTML. Als Datei: Die Icons verweisen auf die Datei, die der Browser cachen kann. Die Datei muss dafür öffentlich erreichbar sein.'
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteModes'] = [
    'inline'   => 'Inline im HTML',
    'external' => 'Als Datei referenzieren',
];

$GLOBALS['TL_LANG']['tl_theme']['svgSpriteSymbols'] = [
    'Symbole im Sprite',
    'Ein Klick kopiert das Insert-Tag in die Zwischenablage.'
];
