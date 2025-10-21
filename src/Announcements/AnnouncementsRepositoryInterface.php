<?php
declare(strict_types=1);

namespace App\Announcements;

use DateTime;

interface AnnouncementsRepositoryInterface
{
    /** @return Announcement[] */
    public function allCurrent(DateTime $now = new DateTime()): array;
}