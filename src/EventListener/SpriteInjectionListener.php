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

namespace Plakart\ContaoSvgSpriteBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Event\LayoutEvent;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Plakart\ContaoSvgSpriteBundle\Sprite\SpriteLocator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adds the inline sprite to the end of the body, for both the legacy page
 * layout (generatePage hook) and the Twig page layout (LayoutEvent, Contao 5.6+).
 */
class SpriteInjectionListener
{
    private const BODY_KEY = 'svg-sprite';

    public function __construct(private readonly SpriteLocator $spriteLocator)
    {
    }

    #[AsHook('generatePage')]
    public function onGeneratePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $this->inject($layout);
    }

    #[AsEventListener(LayoutEvent::class)]
    public function onLayout(LayoutEvent $event): void
    {
        if ($layout = $event->getLayout()) {
            $this->inject($layout);
        }
    }

    private function inject(LayoutModel $layout): void
    {
        if (isset($GLOBALS['TL_BODY'][self::BODY_KEY])) {
            return;
        }

        if (null !== ($markup = $this->spriteLocator->getInlineMarkup($layout))) {
            $GLOBALS['TL_BODY'][self::BODY_KEY] = $markup;
        }
    }
}
