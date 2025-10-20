<?php
declare(strict_types=1);
namespace App\Menu;

use App\Support\Html;

final class NavRenderer{
    //  This constructor injects a `MenuRepository` dependency and stores it as a private, 
    //  read-only property, ensuring it can only be set once during object creation. This 
    //  promotes immutability and cleaner dependency management within the class.
    public function __construct(private readonly MenuRepository $repo) {}

    //  This method generates the HTML for a sidebar navigation menu, marking the current 
    //  page’s item as active by comparing URLs. It includes a toggle button for collapsing 
    //  the sidebar and sanitizes all output to ensure safe rendering of labels, icons, and 
    //  links.
    public function render(string $currentPath): string {
        $items = $this->repo->primary();
        $current = basename(parse_url($currentPath, PHP_URL_PATH) ?: 'index.php');

        ob_start(); ?>
        <nav id="sidebar">
            <ul>
                <li>
                    <button onclick="toggleSidebar()" id="toggle-btn" aria-label="Toggle sidebar">
                        <i id="icon-expand" class="bx bx-chevrons-right hidden"></i>
                        <i id="icon-collapse" class="bx bx-chevrons-left"></i>
                    </button>
                </li>

                <?php foreach ($items as $item):
                    $target = basename(parse_url($item['url'] ?? '', PHP_URL_PATH) ?: '');
                    $isActive = $target === $current || ($target === '' && $current === 'index.php');
                    $idAttr = isset($item['id']) && $item['id'] === 'toggle-theme' ? 'id="toggle-theme"' : ''; ?>
                    
                    <li class="<?= $isActive ? 'active' : '' ?>">
                        <a href="<?= Html::e($item['url'] ?? '#') ?>" <?= $idAttr ?>>
                            <i class="<?= Html::e($item['icon']) ?>"></i>
                            <span><?= Html::e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php
        return (string) ob_get_clean();
    }
}