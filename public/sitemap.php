<?php
/**
 * Dynamic sitemap — served at /sitemap.xml via .htaccess rewrite.
 * Combines static pages + live DB content (events) with real lastmod dates.
 */
declare(strict_types=1);

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');   // don't index the sitemap itself

$base    = 'https://tech-events-msi.onrender.com';
$today   = date('Y-m-d');
$urls    = [];

// ── Helper ────────────────────────────────────────────────────────
function url(string $loc, string $lastmod, string $freq, string $priority, array $alternates = []): array {
    return compact('loc', 'lastmod', 'freq', 'priority', 'alternates');
}

function alts(string $base, string $path): array {
    return [
        'it'      => $base . $path . '?lang=it',
        'en'      => $base . $path . '?lang=en',
        'x-default' => $base . $path,
    ];
}

// ── Static pages ──────────────────────────────────────────────────
$urls[] = url($base . '/',             $today, 'weekly',  '1.0', alts($base, '/'));
$urls[] = url($base . '/register.php', $today, 'monthly', '0.8', alts($base, '/register.php'));
$urls[] = url($base . '/login.php',    $today, 'monthly', '0.5', alts($base, '/login.php'));
$urls[] = url($base . '/privacy.php',  '2026-05-23', 'yearly', '0.3', alts($base, '/privacy.php'));
$urls[] = url($base . '/terms.php',    '2026-05-23', 'yearly', '0.3', alts($base, '/terms.php'));

// ── Archive / Storici pages ───────────────────────────────────────
$archives = [
    '/assets/storici/storico.html'  => ['freq' => 'monthly', 'priority' => '0.6'],
    '/assets/storici/cs2.html'      => ['freq' => 'monthly', 'priority' => '0.5'],
    '/assets/storici/valorant.html' => ['freq' => 'monthly', 'priority' => '0.5'],
    '/assets/storici/dota.html'     => ['freq' => 'monthly', 'priority' => '0.5'],
];
foreach ($archives as $path => $meta) {
    $file = __DIR__ . $path;
    $lastmod = file_exists($file) ? date('Y-m-d', filemtime($file)) : $today;
    $urls[] = url($base . $path, $lastmod, $meta['freq'], $meta['priority']);
}

// ── Dynamic: Events from DB ───────────────────────────────────────
try {
    // Build PDO directly so a connection failure throws (not die()) and is caught below
    require_once __DIR__ . '/../src/EnvLoader.php';
    \App\EnvLoader::load(__DIR__ . '/../.env');

    $host   = getenv('DB_HOST') ?: 'localhost';
    $port   = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'tech_dragons_events';
    $user   = getenv('DB_USER') ?: 'root';
    $pass   = getenv('DB_PASS') ?: '';

    $dsn      = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $sslOpts  = [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
        $sslOpts[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
    }
    $pdo = new PDO($dsn, $user, $pass, $sslOpts);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Each event gets a homepage anchor URL; lastmod is the event's end date
    $stmt = $pdo->query(
        "SELECT idEvento, nome, dataInizio, dataFine
         FROM evento
         ORDER BY dataFine DESC
         LIMIT 200"
    );
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $ev) {
        // Individual anchor on the homepage events section
        $lastmod = $ev['dataFine'] ?? $today;
        // Clamp future dates to today for sitemap validity
        if ($lastmod > $today) $lastmod = $today;

        $urls[] = url(
            $base . '/#events',
            $today,
            'weekly',
            '0.9',
            alts($base, '/')
        );
        break; // The events section is one URL; include only once
    }

    // Tournaments: each tournament day as a distinct content update
    $stmt2 = $pdo->query(
        "SELECT t.idTorneo, t.nomeTorneo, t.giornoSvolgimento, g.nomeGioco
         FROM tornei t
         JOIN giochi g ON t.idGioco = g.idGioco
         ORDER BY t.giornoSvolgimento DESC
         LIMIT 200"
    );
    $tourneys = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Use latest tournament date to freshen the homepage lastmod
    if ($tourneys) {
        $latestTourneyDate = $tourneys[0]['giornoSvolgimento'] ?? $today;
        if ($latestTourneyDate > $today) $latestTourneyDate = $today;
        // Update homepage lastmod to reflect latest tournament
        $urls[0]['lastmod'] = $latestTourneyDate;
    }

} catch (\Throwable $e) {
    // DB unavailable — static pages still served correctly
}

// ── Remove duplicate locs (keep first occurrence) ─────────────────
$seen  = [];
$dedup = [];
foreach ($urls as $u) {
    $loc = $u['loc'];
    if (!isset($seen[$loc])) {
        $seen[$loc] = true;
        $dedup[] = $u;
    }
}
$urls = $dedup;

// ── Output XML ───────────────────────────────────────────────────
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml"' . "\n";
echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$u['freq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    foreach ($u['alternates'] ?? [] as $lang => $href) {
        echo '    <xhtml:link rel="alternate" hreflang="' . htmlspecialchars($lang, ENT_XML1) . '"'
           . ' href="' . htmlspecialchars($href, ENT_XML1) . '"/>' . "\n";
    }
    echo "  </url>\n";
}

echo "</urlset>\n";
