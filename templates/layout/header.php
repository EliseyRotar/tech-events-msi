<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Tech Events — Esports Event Manager'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <nav class="glass-nav">
        <div class="nav-container">
            <a href="/" class="nav-logo">Tech<span>Events</span></a>
            <div class="links">
                <a href="/assets/storici/storico.html">Storico</a>
                <a href="/#tournaments">Tornei</a>
                <a href="/#organizers">Per Organizer</a>
                <?php if (isset($_SESSION['email'])): ?>
                    <a href="/logout.php" class="btn-secondary">Esci</a>
                <?php else: ?>
                    <a href="/login.php" class="btn-primary-outline">Accedi</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
