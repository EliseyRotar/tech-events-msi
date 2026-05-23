<?php
require '../config.php';
\App\Auth::requireLogin();

$idTorneo = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$idTorneo) {
    header('Location: dashboard.php');
    exit;
}

$stm = $pdo->prepare('SELECT nomeTorneo FROM tornei WHERE idTorneo = :id');
$stm->bindParam(':id', $idTorneo);
$stm->execute();
$tournament = $stm->fetch(PDO::FETCH_ASSOC);
if (!$tournament) {
    header('Location: dashboard.php');
    exit;
}

$isAdmin = (int)($_SESSION['admin'] ?? 0) === 1;
$userId  = (int)$_SESSION['id'];

// Non-admins only see teams they belong to
if ($isAdmin) {
    $stm = $pdo->prepare('SELECT idSquadra, nomeSquadra FROM squadre ORDER BY nomeSquadra');
    $stm->execute();
} else {
    $stm = $pdo->prepare(
        'SELECT s.idSquadra, s.nomeSquadra
         FROM squadre s
         JOIN membri m ON m.idSquadra = s.idSquadra
         WHERE m.idUtente = :uid
         ORDER BY s.nomeSquadra'
    );
    $stm->execute([':uid' => $userId]);
}
$teams = $stm->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $idS = (int)$_POST['idSTxt'];

    // Ownership check: verify the user is a member of the selected team
    if (!$isAdmin) {
        $ownerCheck = $pdo->prepare(
            "SELECT idMembro FROM membri WHERE idUtente = :uid AND idSquadra = :sid LIMIT 1"
        );
        $ownerCheck->execute([':uid' => $userId, ':sid' => $idS]);
        if (!$ownerCheck->fetch()) {
            $error = 'You are not a member of that team.';
            goto render;
        }
    }

    try {
        $pdo->beginTransaction();
        $stm = $pdo->prepare(
            "INSERT INTO tornei_squadre (idTorneo, idSquadra) VALUES (:it, :is)"
        );
        $stm->bindParam(':it', $idTorneo);
        $stm->bindParam(':is', $idS);
        $stm->execute();
        $pdo->commit();
        header('Location: /dashboard.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Registration failed: ' . $e->getMessage();
    }
}
render:

$pageTitle = 'Tournament Entry — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
    <div class="container" style="max-width:540px;">
        <div class="form-card" style="margin-top:0;">
            <span class="section-label">Competition Entry</span>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;letter-spacing:-0.8px;margin-bottom:8px;">
                <?= htmlspecialchars($tournament['nomeTorneo'], ENT_QUOTES) ?>
            </h1>
            <p class="lead" style="color:var(--text-secondary);font-size:15px;margin-bottom:32px;line-height:1.6;">
                Register your organisation to compete in this tournament.
            </p>

            <?php if (isset($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="form-group">
                    <label class="form-label" for="idSTxt">Select Your Team</label>
                    <select class="form-select form-input" id="idSTxt" name="idSTxt" required>
                        <option value="">Choose a registered team…</option>
                        <?php foreach ($teams as $t): ?>
                            <option value="<?= (int)$t['idSquadra'] ?>">
                                <?= htmlspecialchars($t['nomeSquadra'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-submit">Confirm Participation</button>
            </form>

            <p style="text-align:center;margin-top:24px;font-size:14px;">
                <a href="/dashboard.php" style="color:var(--text-secondary);">← Cancel and return</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
