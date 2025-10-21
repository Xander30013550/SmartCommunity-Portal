<?php
declare(strict_types=1);

namespace App\Announcements;
require_once __DIR__ . '/AnnouncementsRepositoryInterface.php';

use DateTime;
use PDO;


final class DbAnnouncementsRepository implements AnnouncementsRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    /** @return Announcement[] */
    public function allCurrent(DateTime $now = new DateTime()): array
    {
        $sql = "
            SELECT id, priority, title, body, `start`, `end`, category, link_url, link_text
            FROM announcements
            WHERE ( `start` IS NULL OR `start` <= :today )
              AND ( `end`   IS NULL OR `end`   >= :today )
            ORDER BY
              FIELD(priority, 'high','medium','low') DESC,
              COALESCE(`start`, '1970-01-01') DESC,
              updated_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':today' => $now->format('Y-m-d')]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $items = [];
        foreach ($rows as $r) {
            $items[] = new Announcement([
                'id'       => (string)$r['id'],
                'priority' => $r['priority'] ?? 'medium',
                'title'    => $r['title'] ?? '',
                'body'     => $r['body'] ?? '',
                'start'    => isset($r['start']) ? new DateTime($r['start']) : null,
                'end'      => isset($r['end'])   ? new DateTime($r['end'])   : null,
                'category' => $r['category'] ?? 'General',
                'link'     => ($r['link_url'] ?? null)
                                ? ['url' => $r['link_url'], 'text' => ($r['link_text'] ?: 'Learn more')]
                                : null,
            ]);
        }
        return $items;
    }
}
