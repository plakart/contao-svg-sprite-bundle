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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField(['svgSpriteFile', 'svgSpriteMode'], 'templates')
    ->applyToPalette('default', 'tl_theme')
;

$GLOBALS['TL_DCA']['tl_theme']['fields']['svgSpriteFile'] = [
    'inputType' => 'fileTree',
    'eval'      => ['filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>'svg', 'tl_class'=>'clr'],
    'sql'       => "binary(16) NULL"
];

$GLOBALS['TL_DCA']['tl_theme']['fields']['svgSpriteMode'] = [
    'inputType' => 'select',
    'options'   => ['inline', 'external'],
    'reference' => &$GLOBALS['TL_LANG']['tl_theme']['svgSpriteModes'],
    'eval'      => ['tl_class'=>'w50'],
    'sql'       => "varchar(16) NOT NULL default 'inline'"
];