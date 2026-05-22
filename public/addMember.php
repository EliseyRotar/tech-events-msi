<?php
require '../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireLogin();

$idSquadra = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$idSquadra) {
    header('Location: dashboard.php');
    exit;
}

$stm = $pdo->prepare('SELECT nomeSquadra FROM squadre WHERE idSquadra = :id');
$stm->bindParam(':id', $idSquadra);
$stm->execute();
$team = $stm->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    header('Location: dashboard.php');
    exit;
}

$stm = $pdo->prepare('SELECT idUtente, nome, cognome FROM utenti ORDER BY nome');
$stm->execute();
$users = $stm->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickTxt'] ?? '');
    $idU      = (int)($_POST['idUTxt'] ?? 0);

    if ($idU <= 0) {
        $error = 'Please select a valid user profile.';
    } else {
        $error = runInTransaction($pdo, function() use ($pdo, $nickname, $idSquadra, $idU) {
            $stm = $pdo->prepare(
                "INSERT INTO membri (nickname, idSquadra, idUtente) VALUES (:n, :is, :iu)"
            );
            $stm->bindParam(':n',  $nickname);
            $stm->bindParam(':is', $idSquadra);
            $stm->bindParam(':iu', $idU);
            $stm->execute();
        }, 'dashboard.php');
        if ($error) {
            $error = 'Failed to add member: ' . $error;
        }
    }
}

$pageTitle = 'Manage Roster — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
    <div class="container" style="max-width:540px;">
        <div class="form-card" style="margin-top:0;">
            <span class="section-label">Roster Management</span>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;letter-spacing:-0.8px;margin-bottom:8px;">
                <?= htmlspecialchars($team['nomeSquadra'], ENT_QUOTES) ?>
            </h1>
            <p class="lead" style="color:var(--text-secondary);font-size:15px;margin-bottom:32px;line-height:1.6;">
                Add a new athlete to the organisation roster.
            </p>

            <?php if (isset($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label class="form-label" for="nickTxt">In-Game Nickname</label>
                    <input class="form-input" type="text" id="nickTxt" name="nickTxt"
                           placeholder="s1mple / ZywOo" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idUTxt">User Profile</label>
                    <select class="form-select form-input" id="idUTxt" name="idUTxt" required>
                        <option value="">Select a registered user…</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['idUtente'] ?>">
                                <?= htmlspecialchars($u['nome'] . ' ' . $u['cognome'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-submit">Add to Roster</button>
            </form>

            <p style="text-align:center;margin-top:24px;font-size:14px;">
                <a href="/dashboard.php" style="color:var(--text-secondary);">← Cancel and return</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
