<?php
declare(strict_types=1);
namespace App\Support;

final class Html {
    public static function e(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
