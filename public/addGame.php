<?php
require '../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireAdmin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['nameTxt'] ?? '');
    $copy = trim($_POST['copyTxt'] ?? '');
    $error = runInTransaction($pdo, function() use ($pdo, $name, $copy) {
        $stm = $pdo->prepare('INSERT INTO giochi (nomeGioco, copyright) VALUES (:n, :c)');
        $stm->bindParam(':n', $name);
        $stm->bindParam(':c', $copy);
        $stm->execute();
    }, 'dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game Title — Tech Dragons Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/php-pages.css">
</head>
<body class="page-form">

<div id="page-overlay" aria-hidden="true"><span class="overlay-logo">TD</span></div>
<div id="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" aria-hidden="true"></div>

<nav class="glass-nav scrolled" id="navbar">
    <div class="nav-container">
        <a href="/" class="nav-logo">Tech<span>Dragons</span></a>
        <div class="links">
            <a href="/dashboard.php" class="btn-secondary" style="padding:8px 18px;">Dashboard</a>
        </div>
    </div>
</nav>

<div class="form-page">
    <div class="form-card">
        <span class="section-label">Admin — Game Library</span>
        <h1>Add Game Title</h1>
        <p class="lead">Register a new game discipline to the platform.</p>

        <?php if ($error): ?>
            <div class="error-msg">Error: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label class="form-label" for="nameTxt">Game Name</label>
                <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                       placeholder="e.g. Counter-Strike 2" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="copyTxt">Copyright / Publisher</label>
                <input class="form-input" type="text" id="copyTxt" name="copyTxt"
                       placeholder="e.g. © Valve Corporation">
            </div>

            <button type="submit" class="btn-primary btn-submit">Add Discipline</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;">
            <a href="/dashboard.php" style="color:var(--text-secondary);">← Back to Dashboard</a>
        </p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
