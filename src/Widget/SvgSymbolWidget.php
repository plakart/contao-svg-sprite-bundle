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

namespace Plakart\ContaoSvgSpriteBundle\Widget;

use Contao\System;
use Contao\Widget;
use Plakart\ContaoSvgSpriteBundle\Backend\SymbolGridRenderer;
use Plakart\ContaoSvgSpriteBundle\Sprite\SymbolLibrary;

/**
 * Backend widget to select a symbol of the SVG sprite.
 *
 * 'inputType' => 'svgSymbol',
 * 'eval'      => ['svgSpriteTheme' => 1], // optional: restrict to one theme
 * 'sql'       => "varchar(128) NOT NULL default ''"
 */
class SvgSymbolWidget extends Widget
{
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public function generate(): string
    {
        return System::getContainer()->get(SymbolGridRenderer::class)->renderPicker(
            $this->strName,
            $this->strId,
            (string) $this->varValue,
            $this->getGroups(),
            (bool) $this->mandatory,
        );
    }

    protected function validator($varInput): string
    {
        $value = trim((string) parent::validator($varInput));

        if ('' === $value) {
            return '';
        }

        foreach ($this->getGroups() as $group) {
            foreach ($group['symbols'] as $symbol) {
                if ($symbol['id'] === $value) {
                    return $value;
                }
            }
        }

        $this->addError(\sprintf($GLOBALS['TL_LANG']['MSC']['svgSpriteInvalid'] ?? 'Invalid icon "%s".', $value));

        return $value;
    }

    /**
     * @return array<int, array{name: string, symbols: list<array{id: string, preview: string}>}>
     */
    private function getGroups(): array
    {
        $themeId = $this->svgSpriteTheme ? (int) $this->svgSpriteTheme : null;

        return System::getContainer()->get(SymbolLibrary::class)->getSymbolsByTheme($themeId);
    }
}
