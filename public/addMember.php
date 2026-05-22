<?php
require '../config.php';
require_once __DIR__ . '/../src/helpers.php';
\App\Auth::requireLogin();

$idSquadra = $_GET['id'] ?? null;
if (!$idSquadra) {
    header('Location: dashboard.php');
    exit;
}

// Get team info
$sql = "SELECT nomeSquadra FROM squadre WHERE idSquadra = :id";
$stm = $pdo->prepare($sql);
$stm->bindParam(':id', $idSquadra);
$stm->execute();
$team = $stm->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    header('Location: dashboard.php');
    exit;
}

// Get users to add as members
$sql = "SELECT idUtente, nome, cognome FROM utenti";
$stm = $pdo->prepare($sql);
$stm->execute();
$users = $stm->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Roster — Tech Dragons Events";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickTxt'] ?? '');
    $idU = (int)($_POST['idUTxt'] ?? 0);

    if ($idU <= 0) {
        $error = "Failed to add member: invalid user selection.";
    } else {
        $error = runInTransaction($pdo, function() use ($pdo, $nickname, $idSquadra, $idU) {
            $sql = "INSERT INTO membri (nickname, idSquadra, idUtente) VALUES (:n, :is, :iu)";
            $stm = $pdo->prepare($sql);
            $stm->bindParam(':n', $nickname);
            $stm->bindParam(':is', $idSquadra);
            $stm->bindParam(':iu', $idU);
            $stm->execute();
        }, 'dashboard.php');
        if ($error) {
            $error = "Failed to add member: " . $error;
        }
    }
}

require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container" style="margin-top: 100px; display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <form method="POST" style="margin: 0;">
        <p class="section-label" style="text-align: left; margin-bottom: 8px;">Roster Management</p>
        <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 8px; font-weight: 800;"><?= htmlspecialchars($team['nomeSquadra']) ?></h1>
        <p style="color: var(--text-muted); margin-bottom: 32px;">Add a new professional athlete to the organization roster.</p>

        <?php if (isset($error)): ?>
            <p style="color: #ff3b30; font-weight: 600; text-align: center; margin-bottom: 24px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p>In-Game Nickname</p>
        <input type="text" name="nickTxt" placeholder="s1mple / Zywoo" required>

        <p>Select User Profile</p>
        <select name="idUTxt" required>
            <option value="">Select a registered user</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['idUtente'] ?>"><?= htmlspecialchars($u['nome'] . ' ' . $u['cognome']) ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <button type="submit">Deploy Member</button>
        <p style="text-align: center; margin-top: 24px;"><a href="dashboard.php" style="color: var(--text-muted);">Cancel and return</a></p>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
