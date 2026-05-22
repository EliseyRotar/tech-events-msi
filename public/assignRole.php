<?php
require '../config.php';
\App\Auth::requireLogin();

$idM = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$idM) {
    header('Location: dashboard.php');
    exit;
}

try {
    $stm = $pdo->prepare('SELECT idRuolo, nomeRuolo FROM ruoli ORDER BY nomeRuolo');
    $stm->execute();
    $roles = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $roles = [];
    $error = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = (int)$_POST['role'];
    try {
        $pdo->beginTransaction();
        $stm = $pdo->prepare('INSERT INTO membri_ruoli VALUES (:idR, :idM)');
        $stm->bindParam(':idR', $role);
        $stm->bindParam(':idM', $idM);
        $stm->execute();
        $pdo->commit();
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Role — Tech Dragons Events</title>
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
        <span class="section-label">Roster — Role Assignment</span>
        <h1>Assign Role</h1>
        <p class="lead">Assign a competitive role to member #<?= $idM ?>.</p>

        <?php if (isset($error)): ?>
            <div class="error-msg">Error: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select class="form-select form-input" id="role" name="role" required>
                    <option value="">Select a role…</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['idRuolo'] ?>">
                            <?= htmlspecialchars($r['nomeRuolo'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary btn-submit">Assign Role</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:14px;">
            <a href="/dashboard.php" style="color:var(--text-secondary);">← Cancel</a>
        </p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
