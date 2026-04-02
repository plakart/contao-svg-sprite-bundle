<?php

declare(strict_types=1);

/*
 * This file is part of ContaoSwiperExtensionBundle.
 *
 * (c) plakart GmbH & Co. KG (https://plakart.de)
 * author Jannik Nölke (https://jaynoe.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('svgSpriteFile', 'templates')
    ->applyToPalette('default', 'tl_theme')
;

$GLOBALS['TL_DCA']['tl_theme']['fields']['svgSpriteFile'] = [
    'inputType' => 'fileTree',
    'eval'      => ['filesOnly'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr'],
    'sql'       => "binary(16) NULL"
];