<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$posts = [];
try {
    $stm = $pdo->query(
        "SELECT n.idNotizia, n.titolo, n.contenuto, n.tag, n.pubblicata_il,
                u.nome, u.cognome
         FROM notizie n
         JOIN utenti u ON u.idUtente = n.autore
         ORDER BY n.pubblicata_il DESC
         LIMIT 50"
    );
    $posts = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    // Table may not exist yet
}

$pageTitle = 'News & Announcements — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';

$tagColors = [
    'announcement' => '#00d4ff',
    'tournament'   => '#a78bfa',
    'update'       => '#34c759',
    'event'        => '#ff9500',
];
?>

<main class="page-main">
<div class="container">

    <div class="page-header reveal">
        <div class="page-header-left">
            <span class="section-label">Latest Updates</span>
            <h1>News &amp; Announcements</h1>
        </div>
        <?php if (isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1): ?>
        <div class="page-header-actions">
            <a href="/create-news.php" class="btn-primary">+ New Post</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
    <div style="background:var(--bg-secondary);border:1px dashed var(--border);border-radius:var(--radius-lg);padding:80px;text-align:center;" class="reveal">
        <p style="font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:8px;">No news yet</p>
        <p style="color:var(--text-secondary);">Check back soon for announcements, tournament results, and platform updates.</p>
        <?php if (isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1): ?>
        <a href="/create-news.php" class="btn-primary" style="margin-top:24px;display:inline-flex;">Create First Post</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="display:grid;gap:24px;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));">
        <?php foreach ($posts as $post):
            $excerpt = mb_substr(strip_tags($post['contenuto']), 0, 200);
            if (mb_strlen($post['contenuto']) > 200) $excerpt .= '…';
            $tagColor = $tagColors[$post['tag']] ?? '#00d4ff';
            $dateStr  = date('M j, Y', strtotime($post['pubblicata_il']));
        ?>
        <a href="/news-post.php?id=<?= (int)$post['idNotizia'] ?>" style="text-decoration:none;" class="reveal">
            <article style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;height:100%;display:flex;flex-direction:column;transition:border-color 0.2s,transform 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.4)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='none'">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                    <span style="background:<?= $tagColor ?>22;border:1px solid <?= $tagColor ?>55;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600;color:<?= $tagColor ?>;text-transform:uppercase;letter-spacing:0.5px;">
                        <?= htmlspecialchars($post['tag'], ENT_QUOTES) ?>
                    </span>
                    <span style="font-size:12px;color:var(--text-secondary);"><?= $dateStr ?></span>
                </div>
                <h2 style="font-family:var(--font-display);font-size:18px;font-weight:700;letter-spacing:-0.4px;color:var(--text-primary);margin-bottom:12px;line-height:1.3;">
                    <?= htmlspecialchars($post['titolo'], ENT_QUOTES) ?>
                </h2>
                <p style="color:var(--text-secondary);font-size:14px;line-height:1.7;flex:1;margin-bottom:16px;">
                    <?= htmlspecialchars($excerpt, ENT_QUOTES) ?>
                </p>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:12px;color:var(--text-secondary);">
                        <?= htmlspecialchars($post['nome'] . ' ' . $post['cognome'], ENT_QUOTES) ?>
                    </span>
                    <span style="font-size:12px;color:var(--accent-blue);font-weight:600;">Read more →</span>
                </div>
            </article>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
