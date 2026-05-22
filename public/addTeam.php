<?php 
require '../config.php';
\App\Auth::requireLogin();

$pageTitle = "Register Organization — Tech Dragons Events";

$sql = 'SELECT * FROM sponsor';
$stm = $pdo->prepare($sql);
$stm->execute();
$sponsors = $stm->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['nameTxt']);
    $nComp = (int)$_POST['nCompTxt'];
    $idS = $_POST['idSTxt'];

    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO squadre (nomeSquadra, nComponenti, idSponsor) VALUES (:n, :nc, :ids)";
        $stm = $pdo->prepare($sql);
        $stm->bindParam(':n', $name);
        $stm->bindParam(':nc', $nComp);
        $stm->bindParam(':ids', $idS);
        
        if ($stm->execute()) {
            $pdo->commit();
            header('Location: dashboard.php');
            exit;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to register organization: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../templates/layout/header.php';
?>

<div class="container" style="margin-top: 100px; display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <form method="POST" style="margin: 0;">
        <p class="section-label" style="text-align: left; margin-bottom: 8px;">Organization Management</p>
        <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 32px; font-weight: 800;">New Organization</h1>

        <?php if (isset($error)): ?>
            <p style="color: #ff3b30; font-weight: 600; text-align: center; margin-bottom: 24px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p>Organization Name</p>
        <input type="text" name="nameTxt" placeholder="Team Liquid / NAVI" required>

        <p>Roster Capacity</p>
        <input type="number" name="nCompTxt" placeholder="5" required>

        <p>Primary Sponsor</p>
        <select name="idSTxt" required>
            <option value="">Select a partner</option>
            <?php foreach($sponsors as $s): ?>
                <option value="<?= $s['idSponsor'] ?>"><?= htmlspecialchars($s['nomeAzienda']) ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <button type="submit">Register Organization</button>
        <p style="text-align: center; margin-top: 24px;"><a href="dashboard.php" style="color: var(--text-muted);">Cancel and return</a></p>
    </form>
</div>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
