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

namespace Plakart\ContaoSvgSpriteBundle\Twig;

use Plakart\ContaoSvgSpriteBundle\Sprite\SymbolRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {{ symbol('arrow') }}
 * {{ symbol('phone', {width: 20, height: 20, label: 'Telefon', class: 'kontakt__icon'}) }}.
 */
class SymbolExtension extends AbstractExtension
{
    public function __construct(private readonly SymbolRenderer $renderer)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('symbol', $this->renderer->render(...), ['is_safe' => ['html']]),
        ];
    }
}
