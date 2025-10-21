<?php
declare(strict_types=1);

//  This function escapes special HTML characters in a string to prevent XSS attacks, 
// ensuring the output is safe for rendering in HTML contexts. It uses `htmlspecialchars` 
// with `ENT_QUOTES` and UTF-8 encoding for maximum compatibility and security.
function e(null|bool|int|float|string|\Stringable $s): string
{
    if ($s === null) return '';
    if (is_bool($s)) $s = $s ? '1' : '0';
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
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

//  This function prepares and executes a PDO statement with optional parameters, 
//  then returns all results as an associative array or an empty array if none are found.
function _fetchAllAssoc(PDO $pdo, string $sql, array $params = []): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}