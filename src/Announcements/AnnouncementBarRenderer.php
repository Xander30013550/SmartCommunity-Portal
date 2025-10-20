<?php
declare(strict_types=1);

namespace App\Announcements;

final class AnnouncementBarRenderer {
  //    This constructor initializes the object by injecting an `AnnouncementsRepository` instance
  //    and optionally accepting an array of options, both stored as private properties.
  public function __construct(
        private AnnouncementsRepository $repo,
        private array $options = []
    ) {}

    //  This method fetches current announcements from a repository and generates an accessible, customizable
    //  HTML slider carousel displaying them. It applies default and user-provided options for autoplay, size,
    //  and styling, then outputs each announcement with semantic markup, navigation buttons, and live region 
    //  updates for screen readers; if no announcements exist, it returns a simple informational message instead.
  public function render(): string {
    $items = $this->repo->allCurrent();

    if (!$items) {
        return '<div class="alert info">No current announcements.</div>';
    }

    $opt = $this->options + [
        'autoplay'   => true,
        'interval'   => 4000,
        'size'       => 'md',
        'width'      => '100%',
        'height'     => '',   // if provided, overrides size
        'extraClass' => '',
    ];

    $sizeClass = match ($opt['size']) {
        'sm' => 'slider--sm',
        'lg' => 'slider--lg',
        default => 'slider--md',
    };

    $style = 'style="';
    $style .= 'width:' . htmlspecialchars($opt['width']) . ';';
    if ($opt['height'] !== '') {
        $style .= 'height:' . htmlspecialchars($opt['height']) . ';';
    }
        
    $style .= '"';

    $autoplay = $opt['autoplay'] ? 'true' : 'false';
    $interval = (int)$opt['interval'];
    $extra    = trim($opt['extraClass']);

    ob_start(); ?>
    <section class="slider <?= $sizeClass ?> <?= htmlspecialchars($extra) ?>"
            aria-roledescription="carousel"
            aria-label="Announcements"
            data-autoplay="<?= $autoplay ?>"
            data-interval="<?= $interval ?>"
            <?= $style ?>>
      <div class="slider-track" id="slider-track">
        <?php foreach ($items as $i => $a): ?>
          <figure class="slide priority-<?= htmlspecialchars($a->priority) ?>"
                  aria-roledescription="slide"
                  aria-label="Announcement <?= $i + 1 ?> of <?= count($items) ?>">
            <article class="ann-card">
              <h2 class="ann-title"><?= htmlspecialchars($a->title) ?></h2>
              <p class="ann-body"><?= nl2br(htmlspecialchars($a->body)) ?></p>
              <p class="ann-when">
                <?php
                  $parts = [];
                  if ($a->start) $parts[] = 'From ' . $a->start->format('M j, Y');
                  if ($a->end)   $parts[] = 'until ' . $a->end->format('M j, Y');
                  echo htmlspecialchars(implode(' ', $parts));
                ?>
              </p>
              <?php if (!empty($a->link['url'])): ?>
                <a class="ann-link" href="<?= htmlspecialchars($a->link['url']) ?>" target="_blank" rel="noopener noreferrer">
                  <?= htmlspecialchars($a->link['text'] ?: 'Learn more') ?>
                </a>
              <?php endif; ?>
            </article>
          </figure>
        <?php endforeach; ?>
      </div>
      <button class="nav prev" aria-label="Previous announcement" data-dir="-1">❮</button>
      <button class="nav next" aria-label="Next announcement" data-dir="1">❯</button>
      <div class="dots" id="slider-dots" aria-label="Slide navigation"></div>
      <p class="sr-only" aria-live="polite" id="sr-status"></p>
    </section>

    <?php
        return (string)ob_get_clean();
  }
}