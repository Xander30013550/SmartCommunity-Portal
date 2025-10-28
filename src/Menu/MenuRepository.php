<?php
declare(strict_types=1);
namespace App\Menu;

final class MenuRepository {
    //  This constructor method initializes the class with a read-only string property representing 
    // the configuration directory path. It ensures the directory value is set once and cannot be 
    // changed afterward.
    public function __construct(private readonly string $configDir) {}

    /** @return array<int,array{id:string,label:string,url:string,icon:string,weight:int}> */
    //  This method loads a menu configuration from an XML file in the specified config directory, 
    //  selects the primary menu or the first available menu, then extracts and sorts its items by 
    //  weight and label before returning them as an array. If the file is missing or invalid, it 
    //  safely returns an empty array.
    public function primary(string $file='menus.xml'): array {
        $path = rtrim($this->configDir, '/\\') . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) return [];

        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) { libxml_clear_errors(); return []; }
        libxml_clear_errors();

        $menu = null;
        foreach ($xml->menu as $m) {
            if ((string)($m['id'] ?? '') === 'primary') { $menu = $m; break; }
        }
        if (!$menu && isset($xml->menu[0])) $menu = $xml->menu[0];
        if (!$menu) return [];

        $items = [];
        foreach ($menu->item as $item) {
            $items[] = [
                'id'     => (string)($item['id'] ?? ''),
                'label'  => trim((string)($item->label ?? 'Untitled')),
                'url'    => trim((string)($item->url ?? '#')),
                'icon'   => trim((string)($item->icon ?? 'bx bx-link')),
                'weight' => (int)($item['weight'] ?? 0),
            ];
        }
        usort($items, fn($a,$b) => [$a['weight'],$a['label']] <=> [$b['weight'],$b['label']]);
        return $items;
    }
}