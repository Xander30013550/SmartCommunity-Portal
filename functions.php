<?php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function loadXml(string $path): ?SimpleXMLElement {
    if (!is_file($path)) return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}

function getPrimaryMenuItems(string $menusPath): array {
    $xml = loadXml($menusPath);
    if (!$xml) return [];

    $menu = $xml->menu;
    if (!$menu) {
        foreach ($xml->menu as $m) {
            if ((string)($m['id'] ?? '') === 'primary') {
                $menu = $m;
                break;
            }
        }
    }
    if (!$menu) return [];

    $items = [];
    foreach ($menu->item as $item) {
        $items[] = [
            'id'    => (string)($item['id'] ?? ''),
            'label' => trim((string)($item->label ?? 'Untitled')),
            'url'   => trim((string)($item->url ?? '#')),
            'icon'  => trim((string)($item->icon ?? 'bx bx-link')),
            'weight'=> (int)($item['weight'] ?? 0),
        ];
    }
    usort($items, function ($a, $b) {
        return [$a['weight'], $a['label']] <=> [$b['weight'], $b['label']];
    });

    return $items;
}