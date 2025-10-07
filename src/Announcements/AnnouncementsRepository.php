<?php
declare(strict_types=1);

namespace App\Announcements;
use DateTime;
use SimpleXMLElement;
final class AnnouncementsRepository
{
    public function __construct(
        private string $xmlPath
    ) {
    }

    public function allCurrent(DateTime $now = new DateTime()): array
    {
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
                'priority' => strtolower(trim((string) ($a['priority'] ?? 'normal'))),
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

    private static function parseDate(string $raw, bool $inclusive = false): ?DateTime
    {
        $raw = trim($raw);
        if ($raw === '')
            return null;
        $dt = new DateTime($raw);
        if ($inclusive)
            $dt->setTime(23, 59, 59);
        return $dt;
    }

    private static function weightOf(string $p): int
    {
        return match (strtolower($p)) {
            'high' => 3,
            'normal' => 2,
            'low' => 1,
            default => 1,
        };
    }
}