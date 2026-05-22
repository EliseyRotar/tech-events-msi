<?php
require '../config.php';
\App\Auth::requireLogin();

$stm = $pdo->prepare('SELECT idSponsor, nomeAzienda FROM sponsor ORDER BY nomeAzienda');
$stm->execute();
$sponsors = $stm->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['nameTxt']);
    $nComp = (int)$_POST['nCompTxt'];
    $idS   = $_POST['idSTxt'] ?: null;

    try {
        $pdo->beginTransaction();
        $stm = $pdo->prepare(
            "INSERT INTO squadre (nomeSquadra, nComponenti, idSponsor) VALUES (:n, :nc, :ids)"
        );
        $stm->bindParam(':n',   $name);
        $stm->bindParam(':nc',  $nComp);
        $stm->bindParam(':ids', $idS);
        $stm->execute();
        $pdo->commit();
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to register organisation: ' . $e->getMessage();
    }
}

$pageTitle = 'Register Organisation — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
    <div class="container" style="max-width:540px;">
        <div class="form-card" style="margin-top:0;">
            <span class="section-label">Organisation Management</span>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;letter-spacing:-0.8px;margin-bottom:8px;">Register Organisation</h1>
            <p class="lead" style="color:var(--text-secondary);font-size:15px;margin-bottom:32px;line-height:1.6;">Add your team to the platform to enter tournaments.</p>

            <?php if (isset($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label class="form-label" for="nameTxt">Organisation Name</label>
                    <input class="form-input" type="text" id="nameTxt" name="nameTxt"
                           placeholder="Team Liquid / NAVI" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nCompTxt">Roster Capacity</label>
                    <input class="form-input" type="number" id="nCompTxt" name="nCompTxt"
                           placeholder="5" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idSTxt">Primary Sponsor</label>
                    <select class="form-select form-input" id="idSTxt" name="idSTxt">
                        <option value="">Independent (no sponsor)</option>
                        <?php foreach ($sponsors as $s): ?>
                            <option value="<?= (int)$s['idSponsor'] ?>">
                                <?= htmlspecialchars($s['nomeAzienda'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-submit">Register Organisation</button>
            </form>

            <p style="text-align:center;margin-top:24px;font-size:14px;">
                <a href="/dashboard.php" style="color:var(--text-secondary);">← Cancel and return</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
