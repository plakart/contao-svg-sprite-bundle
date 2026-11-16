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

namespace Plakart\ContaoSvgSpriteBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Plakart\ContaoSvgSpriteBundle\Sprite\SymbolRenderer;

/**
 * {{symbol::ICON_ID}}
 * {{symbol::ICON_ID?width=32&height=32&label=Telefon&class=foo bar}}.
 */
#[AsInsertTag('symbol')]
class SymbolInsertTag
{
    public function __construct(private readonly SymbolRenderer $renderer)
    {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        // Insert tags in rich text content arrive HTML encoded (&amp;)
        $param = html_entity_decode((string) $insertTag->getParameters()->get(0), ENT_QUOTES | ENT_HTML5);

        [$id, $query] = explode('?', $param, 2) + [1 => null];

        $options = [];

        if (null !== $query) {
            parse_str($query, $parameters);

            foreach (['width', 'height', 'label', 'class'] as $name) {
                if (isset($parameters[$name]) && \is_string($parameters[$name])) {
                    $options[$name] = $parameters[$name];
                }
            }
        }

        return new InsertTagResult($this->renderer->render($id, $options), OutputType::html);
    }
}
