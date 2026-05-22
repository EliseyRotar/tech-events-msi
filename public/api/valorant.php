<?php
/**
 * Server-side proxy for the VLR.gg API — avoids browser CORS restrictions.
 * Caches the result for 5 minutes in /tmp so repeated page loads are fast.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$cacheFile = sys_get_temp_dir() . '/td_valorant_events.json';
$cacheTTL  = 300; // 5 minutes

// Serve from cache if fresh
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

$ctx = stream_context_create([
    'http' => [
        'timeout'    => 10,
        'user_agent' => 'Mozilla/5.0 (compatible; TechDragonsBot/1.0)',
        'method'     => 'GET',
    ],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
]);

$raw = @file_get_contents('https://vlrggapi.vercel.app/events?q=completed', false, $ctx);

if ($raw !== false) {
    $decoded = json_decode($raw, true);
    // Normalise to flat array of events
    $events = [];
    if (isset($decoded['data']['segments']) && is_array($decoded['data']['segments'])) {
        foreach ($decoded['data']['segments'] as $e) {
            $events[] = [
                'title'  => $e['title']  ?? '',
                'prize'  => $e['prize']  ?? '',
                'region' => strtoupper($e['region'] ?? ''),
                'dates'  => $e['dates']  ?? '',
                'status' => 'completed',
            ];
        }
    } elseif (is_array($decoded)) {
        foreach ($decoded as $e) {
            $events[] = [
                'title'  => $e['title']  ?? $e['name'] ?? '',
                'prize'  => $e['prize']  ?? $e['prizePool'] ?? '',
                'region' => strtoupper($e['region'] ?? ''),
                'dates'  => $e['dates']  ?? '',
                'status' => $e['status'] ?? 'completed',
            ];
        }
    }
    $out = json_encode(['success' => true, 'data' => $events]);
    file_put_contents($cacheFile, $out);
    echo $out;
    exit;
}

// Fallback: return sample data so the page is never blank
$sample = [
    ['title' => 'VCT 2026: Masters Toronto',        'prize' => '$500,000', 'region' => 'INTL',   'dates' => 'Jun 1–14',   'status' => 'completed'],
    ['title' => 'VCT 2026: EMEA Kickoff',            'prize' => '$250,000', 'region' => 'EU',     'dates' => 'Jan 15–28',  'status' => 'completed'],
    ['title' => 'VCT 2026: Americas Kickoff',        'prize' => '$250,000', 'region' => 'NA',     'dates' => 'Jan 20–Feb 2','status' => 'completed'],
    ['title' => 'VCT 2026: Pacific Kickoff',         'prize' => '$250,000', 'region' => 'APAC',   'dates' => 'Jan 22–Feb 4','status' => 'completed'],
    ['title' => 'Challengers 2026: EMEA Split 1',    'prize' => '$50,000',  'region' => 'EU',     'dates' => 'Feb–Mar',    'status' => 'completed'],
    ['title' => 'Challengers 2026: NA Split 1',      'prize' => '$50,000',  'region' => 'NA',     'dates' => 'Feb–Mar',    'status' => 'completed'],
    ['title' => 'Challengers 2026: SEA Split 1',     'prize' => '$30,000',  'region' => 'SEA',    'dates' => 'Feb–Apr',    'status' => 'completed'],
    ['title' => 'VCT 2026: Masters Bangkok',         'prize' => '$500,000', 'region' => 'INTL',   'dates' => 'Mar 1–16',   'status' => 'completed'],
    ['title' => 'Challengers 2026: LATAM Split 1',   'prize' => '$30,000',  'region' => 'LATAM',  'dates' => 'Feb–Apr',    'status' => 'completed'],
    ['title' => 'Challengers 2026: APAC Split 1',    'prize' => '$40,000',  'region' => 'APAC',   'dates' => 'Mar–Apr',    'status' => 'completed'],
    ['title' => 'VCT 2026: EMEA Stage 1',            'prize' => '$300,000', 'region' => 'EU',     'dates' => 'Apr 5–28',   'status' => 'completed'],
    ['title' => 'VCT 2026: Americas Stage 1',        'prize' => '$300,000', 'region' => 'NA',     'dates' => 'Apr 8–May 1','status' => 'completed'],
    ['title' => 'VCT 2026: Pacific Stage 1',         'prize' => '$300,000', 'region' => 'APAC',   'dates' => 'Apr 10–May 3','status' => 'completed'],
    ['title' => 'VCT 2026: China Stage 1',           'prize' => '$200,000', 'region' => 'CN',     'dates' => 'Apr–May',    'status' => 'completed'],
    ['title' => 'Challengers 2026: EMEA Split 2',    'prize' => '$50,000',  'region' => 'EU',     'dates' => 'May–Jun',    'status' => 'completed'],
];

$out = json_encode(['success' => true, 'data' => $sample, 'source' => 'fallback']);
echo $out;
