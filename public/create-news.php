<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireAdmin();

$error   = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo    = trim($_POST['titolo']    ?? '');
    $contenuto = trim($_POST['contenuto'] ?? '');
    $tag       = in_array($_POST['tag'] ?? '', ['announcement','tournament','update','event'], true)
                   ? $_POST['tag'] : 'announcement';
    $imgUrl    = trim($_POST['immagine_url'] ?? '') ?: null;

    if ($titolo === '' || $contenuto === '') {
        $error = 'Title and content are required.';
    } else {
        try {
            $stm = $pdo->prepare(
                "INSERT INTO notizie (titolo, contenuto, immagine_url, tag, autore)
                 VALUES (:t, :c, :i, :g, :a)"
            );
            $stm->execute([
                ':t' => $titolo,
                ':c' => $contenuto,
                ':i' => $imgUrl,
                ':g' => $tag,
                ':a' => $_SESSION['id'],
            ]);
            header('Location: /news.php');
            exit;
        } catch (\PDOException $e) {
            $error = 'Failed to create post: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Create News Post — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container" style="max-width:700px;">

    <a href="/news.php" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-secondary);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:32px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 3L5 8l5 5"/></svg>
        News
    </a>

    <div class="form-card reveal">
        <span class="section-label">Admin</span>
        <h1>Create News Post</h1>
        <p class="lead">Publish an announcement, tournament result, or platform update.</p>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="titolo">Title</label>
                <input class="form-input" type="text" id="titolo" name="titolo"
                       value="<?= htmlspecialchars($_POST['titolo'] ?? '', ENT_QUOTES) ?>"
                       placeholder="e.g. CS2 Championship Results — Spring 2026" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="tag">Category</label>
                <select class="form-select form-input" id="tag" name="tag">
                    <?php
                    $tags = ['announcement' => 'Announcement', 'tournament' => 'Tournament', 'update' => 'Platform Update', 'event' => 'Event'];
                    foreach ($tags as $val => $label): ?>
                    <option value="<?= $val ?>" <?= (($_POST['tag'] ?? 'announcement') === $val) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="contenuto">Content</label>
                <textarea class="form-textarea" id="contenuto" name="contenuto"
                          rows="10" placeholder="Write your post content here…" required><?= htmlspecialchars($_POST['contenuto'] ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="immagine_url">Image URL <span style="color:var(--text-secondary);font-weight:400;">(optional)</span></label>
                <input class="form-input" type="url" id="immagine_url" name="immagine_url"
                       value="<?= htmlspecialchars($_POST['immagine_url'] ?? '', ENT_QUOTES) ?>"
                       placeholder="https://…">
            </div>

            <button type="submit" class="btn-primary btn-submit">Publish Post</button>
        </form>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
