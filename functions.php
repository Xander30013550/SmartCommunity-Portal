<?php
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function loadXml(string $path): ?SimpleXMLElement
{
    if (!is_file($path))
        return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}