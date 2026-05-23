<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /news.php');
    exit;
}

$post = null;
try {
    $stm = $pdo->prepare(
        "SELECT n.*, u.nome, u.cognome
         FROM notizie n
         JOIN utenti u ON u.idUtente = n.autore
         WHERE n.idNotizia = :id"
    );
    $stm->execute([':id' => $id]);
    $post = $stm->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) { /* table may not exist */ }

if (!$post) {
    header('Location: /news.php');
    exit;
}

$tagColors = [
    'announcement' => '#00d4ff',
    'tournament'   => '#a78bfa',
    'update'       => '#34c759',
    'event'        => '#ff9500',
];
$tagColor = $tagColors[$post['tag']] ?? '#00d4ff';
$dateStr  = date('F j, Y', strtotime($post['pubblicata_il']));

$pageTitle = htmlspecialchars($post['titolo'], ENT_QUOTES) . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container" style="max-width:760px;">

    <a href="/news.php" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:40px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        All News
    </a>

    <article class="reveal">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <span style="background:<?= $tagColor ?>22;border:1px solid <?= $tagColor ?>55;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;color:<?= $tagColor ?>;text-transform:uppercase;letter-spacing:0.5px;">
                <?= htmlspecialchars($post['tag'], ENT_QUOTES) ?>
            </span>
            <span style="font-size:13px;color:var(--text-secondary);"><?= $dateStr ?></span>
        </div>

        <h1 style="font-size:clamp(24px,4vw,40px);font-weight:800;letter-spacing:-1px;line-height:1.15;margin-bottom:16px;">
            <?= htmlspecialchars($post['titolo'], ENT_QUOTES) ?>
        </h1>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:40px;padding-bottom:32px;border-bottom:1px solid var(--border);">
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:11px;color:var(--accent-blue);">
                <?= htmlspecialchars(strtoupper(substr($post['nome'], 0, 1) . substr($post['cognome'], 0, 1)), ENT_QUOTES) ?>
            </div>
            <span style="font-size:14px;font-weight:500;color:var(--text-primary);">
                <?= htmlspecialchars($post['nome'] . ' ' . $post['cognome'], ENT_QUOTES) ?>
            </span>
            <span style="color:var(--text-secondary);font-size:13px;">· Tech Dragons Events</span>
        </div>

        <?php if ($post['immagine_url']): ?>
        <img src="<?= htmlspecialchars($post['immagine_url'], ENT_QUOTES) ?>"
             alt="<?= htmlspecialchars($post['titolo'], ENT_QUOTES) ?>"
             style="width:100%;border-radius:var(--radius-lg);margin-bottom:40px;object-fit:cover;max-height:440px;">
        <?php endif; ?>

        <div style="font-size:16px;line-height:1.8;color:var(--text-primary);">
            <?= nl2br(htmlspecialchars($post['contenuto'], ENT_QUOTES)) ?>
        </div>
    </article>

    <div style="margin-top:48px;padding-top:32px;border-top:1px solid var(--border);display:flex;gap:12px;flex-wrap:wrap;">
        <a href="/news.php" class="btn-secondary">← Back to News</a>
        <a href="/#events" class="btn-primary">View Events →</a>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
