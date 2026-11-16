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

namespace Plakart\ContaoSvgSpriteBundle\Sprite;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\ThemeModel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Finds the sprite file of a theme and provides it inline or as URL.
 *
 * Lookups are cached per page and theme, because the renderer asks for every
 * single icon. The caches are cleared via kernel.reset (ResetInterface), so
 * long-running processes (e.g. FrankenPHP worker mode) see changes.
 */
class SpriteLocator implements ResetInterface
{
    public const MODE_INLINE = 'inline';
    public const MODE_EXTERNAL = 'external';

    /**
     * @var array<int, array{path: string, mode: string, url: string}|null>
     */
    private array $spriteByTheme = [];

    /**
     * @var array<int, int|null>
     */
    private array $themeIdByPage = [];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function reset(): void
    {
        $this->spriteByTheme = [];
        $this->themeIdByPage = [];
    }

    /**
     * Returns the sprite markup for the given layout, wrapped in a hidden
     * container, or null if the theme has no sprite or references it
     * externally.
     */
    public function getInlineMarkup(LayoutModel $layout): string|null
    {
        $sprite = $this->getSpriteForTheme((int) $layout->pid);

        if (null === $sprite || self::MODE_INLINE !== $sprite['mode']) {
            return null;
        }

        $content = file_get_contents($this->projectDir.'/'.$sprite['path']);

        if (false === $content) {
            return null;
        }

        // Remove XML declaration, doctype and leading comments, which are
        // not allowed (or useless) inside an HTML document.
        $content = preg_replace(['/<\?xml.*?\?>/s', '/<!DOCTYPE[^>]*>/i', '/^\s*<!--.*?-->/s'], '', $content);

        // The hidden attribute does not depend on inline styles and therefore
        // works with a strict Content Security Policy.
        return '<div hidden>'.trim((string) $content).'</div>';
    }

    /**
     * Returns the URL of the sprite file (including a cache busting parameter)
     * if the theme of the current page references it externally, otherwise
     * null (symbols then refer to the inline sprite in the same document).
     */
    public function getExternalUrlForCurrentPage(): string|null
    {
        $themeId = $this->getCurrentThemeId();

        if (null === $themeId) {
            return null;
        }

        $sprite = $this->getSpriteForTheme($themeId);

        if (null === $sprite || self::MODE_EXTERNAL !== $sprite['mode']) {
            return null;
        }

        return $sprite['url'];
    }

    /**
     * @return array{path: string, mode: string, url: string}|null
     */
    private function getSpriteForTheme(int $themeId): array|null
    {
        if (\array_key_exists($themeId, $this->spriteByTheme)) {
            return $this->spriteByTheme[$themeId];
        }

        $this->framework->initialize();

        $theme = $this->framework->getAdapter(ThemeModel::class)->findById($themeId);
        $file = $theme?->svgSpriteFile ? $this->framework->getAdapter(FilesModel::class)->findByUuid($theme->svgSpriteFile) : null;

        if (null === $file || 'file' !== $file->type || !is_file($this->projectDir.'/'.$file->path)) {
            return $this->spriteByTheme[$themeId] = null;
        }

        $basePath = $this->requestStack->getMainRequest()?->getBasePath() ?? '';
        $url = $basePath.'/'.implode('/', array_map('rawurlencode', explode('/', $file->path)));
        $mtime = @filemtime($this->projectDir.'/'.$file->path);

        return $this->spriteByTheme[$themeId] = [
            'path' => $file->path,
            'mode' => self::MODE_EXTERNAL === $theme->svgSpriteMode ? self::MODE_EXTERNAL : self::MODE_INLINE,
            'url' => false !== $mtime ? $url.'?v='.$mtime : $url,
        ];
    }

    private function getCurrentThemeId(): int|null
    {
        $page = $this->getCurrentPage();

        if (null === $page) {
            return null;
        }

        $pageId = (int) $page->id;

        if (\array_key_exists($pageId, $this->themeIdByPage)) {
            return $this->themeIdByPage[$pageId];
        }

        $page->loadDetails();

        $layout = $page->layout ? $this->framework->getAdapter(LayoutModel::class)->findById($page->layout) : null;

        return $this->themeIdByPage[$pageId] = $layout ? (int) $layout->pid : null;
    }

    /**
     * PageFinder::getCurrentPage() does not exist in Contao 5.3, so read the
     * request attribute directly (same logic).
     */
    private function getCurrentPage(): PageModel|null
    {
        foreach ([$this->requestStack->getCurrentRequest(), $this->requestStack->getMainRequest()] as $request) {
            $page = $request?->attributes->get('pageModel');

            if ($page instanceof PageModel) {
                return $page;
            }
        }

        $page = $GLOBALS['objPage'] ?? null;

        return $page instanceof PageModel ? $page : null;
    }
}
