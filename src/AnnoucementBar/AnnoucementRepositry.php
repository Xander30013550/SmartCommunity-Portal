<?php
declare(strict_types=1);
namespace App\AnnoucementBar;

final class annoucementRepositry {
    public function __construct(private readonly string $configDir) {}

    /** @return array<int,array{id:string,label:string,url:string,icon:string,weight:int}> */
    public function primary(string $file='annoucement.xml'): array {
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