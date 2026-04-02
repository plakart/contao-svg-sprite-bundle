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

namespace Plakart\ContaoSvgSpriteBundle\EventListener\Hooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\File;
use Contao\FilesModel;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\ThemeModel;

#[AsHook('generatePage')]
class GeneratePageListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $theme = ThemeModel::findOneBy('id', $layout->pid);
        $spriteSvg = $theme ? FilesModel::findByUuid($theme->svgSpriteFile) : null;

        if($theme && $spriteSvg)
        {
            $file = new File($spriteSvg->path);
            $GLOBALS['TL_BODY'][] = $file->getContent();
        }
    }
}