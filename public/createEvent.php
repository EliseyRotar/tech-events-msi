<?php
require '../config.php';
\App\Auth::requireAdmin();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['nameTxt']   ?? '');
    $nSits  = trim($_POST['sitsTxt']   ?? '');
    $city   = trim($_POST['cityTxt']   ?? '');
    $region = trim($_POST['regionTxt'] ?? '');
    $dateS  = trim($_POST['dateSTxt']  ?? '');
    $dateE  = trim($_POST['dateETxt']  ?? '');

    try {
        $pdo->beginTransaction();
        $stm = $pdo->prepare(
            'INSERT INTO evento (nome, nPosti, citta, paese, dataInizio, dataFine)
             VALUES (:n, :s, :c, :r, :ds, :de)'
        );
        $stm->bindParam(':n',  $name);
        $stm->bindParam(':s',  $nSits);
        $stm->bindParam(':c',  $city);
        $stm->bindParam(':r',  $region);
        $stm->bindParam(':ds', $dateS);
        $stm->bindParam(':de', $dateE);
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
    <title>Create Event — Tech Dragons Events</title>
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
        <span class="section-label">Admin — Event Management</span>
        <h1>Create New Event</h1>
        <p class="lead">Configure a new competition event. You can add tournaments after creation.</p>

        <?php if ($error): ?>
            <div class="error-msg">Error: <?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="nameTxt">Event Name</label>
                <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                       placeholder="e.g. Dragon Cup 2026" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="sitsTxt">Capacity (Slots)</label>
                <input class="form-input" type="number" id="sitsTxt" name="sitsTxt"
                       placeholder="512" min="1" required>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="cityTxt">City</label>
                    <input class="form-input" type="text" id="cityTxt" name="cityTxt"
                           placeholder="Stockholm (leave blank for Online)">
                </div>
                <div class="form-group">
                    <label class="form-label" for="regionTxt">Country</label>
                    <input class="form-input" type="text" id="regionTxt" name="regionTxt"
                           placeholder="Sweden">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="dateSTxt">Start Date</label>
                    <input class="form-input" type="date" id="dateSTxt" name="dateSTxt" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="dateETxt">End Date</label>
                    <input class="form-input" type="date" id="dateETxt" name="dateETxt" required>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-submit">Create Event</button>
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
