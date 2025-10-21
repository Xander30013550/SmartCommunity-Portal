<?php
declare(strict_types=1);
namespace App\Support;

final class Html {
    //  This function safely escapes a string for HTML output by converting special characters to their
    //  corresponding HTML entities, using UTF-8 encoding and handling both quotes and invalid characters.
    //  It helps prevent security issues like XSS when displaying user-generated content.
    public static function e(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
