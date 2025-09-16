<?php 
declare(strict_types=1);

namespace App\Announcements;
use DateTime;
use SimpleXMLElement;
final class Announcement
{
    public string $id       = '';
    public string $priority = 'normal'; // high|normal|low
    public string $title    = '';
    public string $body     = '';
    public ?DateTime $start = null;
    public ?DateTime $end   = null;
    public string $category = 'General';
    public ?array $link     = null;     // ['url' => '', 'text' => '']

    public function __construct(array $data) {
        foreach ($data as $k => $v) $this->$k = $v;
    }
}