<?php
require '../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireAdmin();

$idE = $_GET['id'] ?? null;
if (!$idE) {
    header('Location: dashboard.php');
    exit;
}

$stm = $pdo->prepare('SELECT idGioco, nomeGioco FROM giochi ORDER BY nomeGioco');
$stm->execute();
$gamesInfo = $stm->fetchAll(PDO::FETCH_ASSOC);

$stm2 = $pdo->prepare('SELECT nome FROM evento WHERE idEvento = :id');
$stm2->bindParam(':id', $idE);
$stm2->execute();
$event = $stm2->fetch(PDO::FETCH_ASSOC);
if (!$event) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name  = trim($_POST['nameTxt']  ?? '');
    $money = trim($_POST['moneyTxt'] ?? '');
    $date  = trim($_POST['dateSTxt'] ?? '');
    $idG   = (int)($_POST['gioco']   ?? 0);

    try {
        $pdo->beginTransaction();
        $stm = $pdo->prepare(
            'INSERT INTO tornei (nomeTorneo, montePremi, giornoSvolgimento, idEvento, idGioco)
             VALUES (:n, :m, :d, :idE, :idG)'
        );
        $stm->bindParam(':n',   $name);
        $stm->bindParam(':m',   $money);
        $stm->bindParam(':d',   $date);
        $stm->bindParam(':idE', $idE);
        $stm->bindParam(':idG', $idG);
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
    <title>Add Tournament — Tech Dragons Events</title>
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
    <div class="form-card form-card-wide">
        <span class="section-label">Admin — Tournament Management</span>
        <h1>Add Tournament</h1>
        <p class="lead">
            Adding to: <strong><?= htmlspecialchars($event['nome'], ENT_QUOTES) ?></strong>
        </p>

        <?php if ($error): ?>
            <div class="error-msg">Error: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label class="form-label" for="nameTxt">Tournament Name</label>
                <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                       placeholder="e.g. CS2 Open Bracket" required>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="moneyTxt">Prize Pool (€)</label>
                    <input class="form-input" type="number" id="moneyTxt" name="moneyTxt"
                           placeholder="50000" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="dateSTxt">Tournament Date</label>
                    <input class="form-input" type="date" id="dateSTxt" name="dateSTxt" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="gioco">Game Title</label>
                <select class="form-select form-input" id="gioco" name="gioco" required>
                    <option value="">Select a game…</option>
                    <?php foreach ($gamesInfo as $game): ?>
                        <option value="<?= (int)$game['idGioco'] ?>">
                            <?= htmlspecialchars($game['nomeGioco'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary btn-submit">Create Tournament</button>
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
