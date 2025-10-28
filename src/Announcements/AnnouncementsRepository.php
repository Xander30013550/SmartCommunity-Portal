<?php
declare(strict_types=1);

namespace App\Announcements;
require_once __DIR__ . '/AnnouncementsRepositoryInterface.php';
use DateTime;
use SimpleXMLElement;
final class AnnouncementsRepository implements AnnouncementsRepositoryInterface {
    //  This constructor accepts a string parameter `$xmlPath` and assigns it
    //  to a private property of the same name within the class.
    public function __construct(private string $xmlPath){ }

    //  This method loads announcements from an XML file at `$this->xmlPath`, safely 
    //  extracting all `<announcement>` nodes regardless of their nesting structure. 
    //  It filters announcements to include only those currently active based on optional
    //  start and end dates compared to `$now`, creates `Announcement` objects from the 
    //  valid data, then sorts them by priority (highest first) and earliest end date before 
    //  returning the sorted array.
    public function allCurrent(DateTime $now = new DateTime()): array {
        $xml = @simplexml_load_file($this->xmlPath, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false)
            return [];

        // Collect nodes defensively (support <announcement> or <announcements><announcement>…)
        $nodes = [];

        // Direct children: <announcements><announcement>…</announcement></announcements>
        if (isset($xml->announcement)) {
            foreach ($xml->announcement as $a) {
                $nodes[] = $a;
            }
        }

        // Nested container: <root><announcements><announcement>…</announcement></announcements></root>
        if (isset($xml->announcements) && isset($xml->announcements->announcement)) {
            foreach ($xml->announcements->announcement as $a) {
                $nodes[] = $a;
            }
        }

        // Fallback: XPath any depth
        if (empty($nodes)) {
            $nodes = $xml->xpath('//announcement') ?: [];
        }

        $items = [];
        foreach ($nodes as $a) {
            /** @var SimpleXMLElement $a */
            $start = self::parseDate((string) ($a->start ?? ''));
            $end = self::parseDate((string) ($a->end ?? ''), inclusive: true); // end of day

            $okStart = !$start || $now >= $start;
            $okEnd = !$end || $now <= $end; // inclusive
            if (!($okStart && $okEnd))
                continue;

            $items[] = new Announcement([
                'id' => (string) ($a['id'] ?? ''),
                'priority' => strtolower(trim((string) ($a['priority'] ?? 'medium'))),
                'title' => trim((string) $a->title),
                'body' => trim((string) $a->body),
                'start' => $start,
                'end' => $end,
                'link' => (isset($a->link) && isset($a->link['url']))
                    ? ['url' => (string) $a->link['url'], 'text' => (string) ($a->link['text'] ?? 'Learn more')]
                    : null,
            ]);
        }

        usort($items, function (Announcement $a, Announcement $b) {
            $w = self::weightOf($b->priority) <=> self::weightOf($a->priority);
            if ($w !== 0)
                return $w;

            $ae = $a->end?->getTimestamp() ?? PHP_INT_MAX; // nulls last
            $be = $b->end?->getTimestamp() ?? PHP_INT_MAX;
            return $ae <=> $be; // earliest ending first
        });

        return $items;
    }

    //  This method converts a date string into a `DateTime` object, returning `null` if the input is empty,
    //  and if the `$inclusive` flag is true, it sets the time to the end of the day (23:59:59) to treat the 
    //  date as inclusive.
    private static function parseDate(string $raw, bool $inclusive = false): ?DateTime {
        $raw = trim($raw);
        if ($raw === '')
            return null;
        $dt = new DateTime($raw);
        if ($inclusive)
            $dt->setTime(23, 59, 59);
        return $dt;
    }

    //  This method assigns a numeric weight to a priority string—returning 3 for 'high', 2 for 'medium', 
    // 1 for 'low' or any other unspecified value—to help with sorting or ranking.
    private static function weightOf(string $p): int {
        return match (strtolower($p)) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };
    }
}