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

namespace Plakart\ContaoSvgSpriteBundle\EventListener\Hooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('replaceInsertTags')]
class ReplaceInsertTagsListener
{
    public function __invoke(string $tag)
    {
        [$name, $param] = explode('::', $tag) + [null, null];

        if ('symbol' !== $name) {
            return false;
        }

        $param = html_entity_decode((string) $param, ENT_QUOTES | ENT_HTML5);

        [$icon, $query] = explode('?', $param, 2) + [null, null];

        $attributes = [];

        if (null !== $query) {
            parse_str($query, $parameters);

            foreach (['width', 'height'] as $attribute) {
                if (!isset($parameters[$attribute]) || '' === $parameters[$attribute]) {
                    continue;
                }

                $attributes[] = sprintf(
                    ' %s="%s"',
                    $attribute,
                    htmlspecialchars((string) $parameters[$attribute], ENT_QUOTES | ENT_HTML5),
                );
            }
        }

        $icon = htmlspecialchars((string) $icon, ENT_QUOTES | ENT_HTML5);

        return sprintf(
            '<svg class="sprite-svg-icon icon--%1$s"%2$s><use href="#%1$s"/></svg>',
            $icon,
            implode('', $attributes),
        );
    }
}
