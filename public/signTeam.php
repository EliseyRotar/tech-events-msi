<?php 
require '../config.php';
\App\Auth::requireLogin();

$idTorneo = $_GET['id'] ?? null;
if (!$idTorneo) {
    header('Location: dashboard.php');
    exit;
}

// Get tournament info
$sql = "SELECT nomeTorneo FROM tornei WHERE idTorneo = :id";
$stm = $pdo->prepare($sql);
$stm->bindParam(':id', $idTorneo);
$stm->execute();
$tournament = $stm->fetch(PDO::FETCH_ASSOC);

// Get available teams
$sql = "SELECT idSquadra, nomeSquadra FROM squadre";
$stm = $pdo->prepare($sql);
$stm->execute();
$teams = $stm->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Tournament Registration — Tech Events";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idS = $_POST['idSTxt'];

    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO tornei_squadre (idTorneo, idSquadra) VALUES (:it, :is)";
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':it', $idTorneo);
        $stm->bindParam(':is', $idS);
        
        if ($stm->execute()) {
            $pdo->commit();
            header('Location: dashboard.php?id=' . $_GET['event_id']); // Optional event_id for redirect
            exit;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Registration failed: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container" style="margin-top: 100px; display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <form method="POST" style="margin: 0;">
        <p class="section-label" style="text-align: left; margin-bottom: 8px;">Competition Entry</p>
        <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 8px; font-weight: 800;"><?= htmlspecialchars($tournament['nomeTorneo']) ?></h1>
        <p style="color: var(--text-muted); margin-bottom: 32px;">Register your organization for the upcoming competition.</p>

        <?php if (isset($error)): ?>
            <p style="color: #ff3b30; font-weight: 600; text-align: center; margin-bottom: 24px;"><?= $error ?></p>
        <?php endif; ?>

        <p>Select Your Team</p>
        <select name="idSTxt" required>
            <option value="">Select a registered team</option>
            <?php foreach($teams as $t): ?>
                <option value="<?= $t['idSquadra'] ?>"><?= htmlspecialchars($t['nomeSquadra']) ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <button type="submit">Confirm Participation</button>
        <p style="text-align: center; margin-top: 24px;"><a href="dashboard.php" style="color: var(--text-muted);">Cancel and return</a></p>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
