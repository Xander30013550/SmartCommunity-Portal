<?php
//  This function escapes special HTML characters in a string to prevent XSS attacks, 
// ensuring the output is safe for rendering in HTML contexts. It uses `htmlspecialchars` 
// with `ENT_QUOTES` and UTF-8 encoding for maximum compatibility and security.
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//  This function attempts to load and parse an XML file from a given path, returning a 
//  `SimpleXMLElement` object on success or `null` if the file doesn’t exist or parsing 
//  fails. It uses `LIBXML_NONET` and `LIBXML_NOCDATA` for secure and consistent XML handling.
function loadXml(string $path): ?SimpleXMLElement {
    if (!is_file($path))
        return null;
    $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    return $xml !== false ? $xml : null;
}