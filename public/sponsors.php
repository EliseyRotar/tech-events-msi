<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/helpers.php';

$sent  = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $name    = trim($_POST['contact_name'] ?? '');
    $email   = trim($_POST['contact_email'] ?? '');
    $tier    = trim($_POST['tier'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($company === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        $error = t('sponsors_form_err');
    } else {
        $subject = "Partnership Inquiry — {$company} [{$tier}]";
        $text = "New partnership inquiry from Tech Dragons Events contact form.\n\n"
              . "Company: {$company}\n"
              . "Contact: {$name}\n"
              . "Email: {$email}\n"
              . "Tier Interest: {$tier}\n\n"
              . "Message:\n{$message}\n\n"
              . "---\nSent via tech-events-msi.onrender.com/sponsors.php";

        $ok = \App\Mailer::send(
            getenv('CONTACT_EMAIL') ?: 'techdragonevents@gmail.com',
            $subject,
            $text
        );
        if ($ok) {
            $sent = true;
        } else {
            $error = t('sponsors_form_err');
        }
    }
}

// Pull live stats
$stats = ['players' => 0, 'events' => 0, 'tournaments' => 0, 'teams' => 0];
try {
    $row = $pdo->query(
        "SELECT (SELECT COUNT(*) FROM utenti)  AS players,
                (SELECT COUNT(*) FROM evento)   AS events,
                (SELECT COUNT(*) FROM tornei)   AS tournaments,
                (SELECT COUNT(*) FROM squadre)  AS teams"
    )->fetch(\PDO::FETCH_ASSOC);
    if ($row) $stats = $row;
} catch (\PDOException $e) {}

$pageTitle = t('sponsors_title') . ' — Tech Dragons Events';
require_once __DIR__ . '/../templates/layout/header.php';
?>

<main class="page-main">
<div class="container">

    <!-- Hero -->
    <div class="reveal" style="max-width:760px;margin-bottom:72px;padding-top:8px;">
        <span class="section-label"><?= t('sponsors_label') ?></span>
        <h1 style="font-size:clamp(32px,5vw,56px);font-weight:800;letter-spacing:-2px;margin-bottom:20px;line-height:1.05;">
            <?= t('sponsors_title') ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:17px;line-height:1.75;max-width:600px;margin-bottom:32px;">
            <?= t('sponsors_sub') ?>
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="#inquiry" class="btn-primary" style="padding:14px 32px;font-size:15px;"><?= t('sponsors_form_send') ?> →</a>
            <a href="#tiers" class="btn-secondary" style="padding:14px 32px;font-size:15px;"><?= t('sponsors_tiers_title') ?></a>
        </div>
    </div>

    <!-- Live stats -->
    <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1px;background:var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:80px;">
        <?php
        $statItems = [
            [(int)$stats['players'],     t('sponsors_stat_players')],
            [(int)$stats['events'],      t('sponsors_stat_events')],
            [(int)$stats['tournaments'],  t('sponsors_stat_tournaments')],
            [(int)$stats['teams'],       t('sponsors_stat_teams')],
        ];
        foreach ($statItems as [$val, $lbl]):
        ?>
        <div style="background:var(--bg-secondary);padding:28px 24px;">
            <div style="font-family:var(--font-display);font-size:36px;font-weight:800;color:var(--accent-blue);letter-spacing:-1.5px;line-height:1;">
                <?= number_format($val) ?>+
            </div>
            <div style="font-size:12px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.08em;margin-top:6px;">
                <?= $lbl ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Why Partner -->
    <div class="reveal" style="margin-bottom:80px;">
        <span class="section-label"><?= t('sponsors_why_label') ?></span>
        <h2 style="font-size:clamp(24px,3.5vw,38px);font-weight:800;letter-spacing:-1px;margin-bottom:12px;max-width:600px;line-height:1.15;">
            <?= t('sponsors_why_title') ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:15px;max-width:600px;line-height:1.7;margin-bottom:40px;">
            <?= t('sponsors_why_sub') ?>
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;">
            <?php
            $reasons = [
                ['🎯', t('sponsors_why_1_title'), t('sponsors_why_1_desc')],
                ['🔗', t('sponsors_why_2_title'), t('sponsors_why_2_desc')],
                ['📈', t('sponsors_why_3_title'), t('sponsors_why_3_desc')],
            ];
            foreach ($reasons as [$icon, $title, $desc]):
            ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(0,212,255,0.3)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:28px;margin-bottom:14px;"><?= $icon ?></div>
                <div style="font-family:var(--font-display);font-size:16px;font-weight:700;margin-bottom:8px;"><?= htmlspecialchars($title, ENT_QUOTES) ?></div>
                <div style="font-size:14px;color:var(--text-secondary);line-height:1.65;"><?= htmlspecialchars($desc, ENT_QUOTES) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tier Cards -->
    <div class="reveal" id="tiers" style="margin-bottom:80px;">
        <span class="section-label"><?= t('sponsors_tiers_label') ?></span>
        <h2 style="font-size:clamp(24px,3.5vw,38px);font-weight:800;letter-spacing:-1px;margin-bottom:10px;"><?= t('sponsors_tiers_title') ?></h2>
        <p style="color:var(--text-secondary);font-size:15px;margin-bottom:40px;"><?= t('sponsors_tiers_sub') ?></p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px;">

            <!-- Title Sponsor -->
            <div style="background:linear-gradient(135deg,rgba(255,215,0,0.06),rgba(255,215,0,0.02));border:1px solid rgba(255,215,0,0.4);border-radius:var(--radius-lg);padding:28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;right:0;background:rgba(255,215,0,0.12);border-bottom-left-radius:var(--radius);padding:4px 14px;font-size:10px;font-weight:700;color:#FFD700;letter-spacing:0.08em;text-transform:uppercase;">⭐ Top</div>
                <div style="font-size:28px;margin-bottom:12px;">🏆</div>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:#FFD700;margin-bottom:6px;">Title Sponsor</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;line-height:1.5;">Hardware bundle, major presence, naming rights on flagship tournament.</div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $titleBenefits = [
                        'Logo in site header — every page',
                        '"Powered by [Brand]" naming rights',
                        '5 dedicated social posts / event',
                        'LAN event physical branding',
                        'Product placement in prize pools',
                        'Quarterly audience report',
                        'Co-branded event pages',
                    ];
                    foreach ($titleBenefits as $b):
                    ?>
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-primary);">
                        <span style="color:#FFD700;margin-top:1px;flex-shrink:0;">✓</span>
                        <?= htmlspecialchars($b, ENT_QUOTES) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Gold Partner -->
            <div style="background:linear-gradient(135deg,rgba(0,212,255,0.06),rgba(0,212,255,0.02));border:1px solid rgba(0,212,255,0.35);border-radius:var(--radius-lg);padding:28px;">
                <div style="font-size:28px;margin-bottom:12px;">🥇</div>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--accent-blue);margin-bottom:6px;">Gold Partner</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;line-height:1.5;">Strong brand integration across events and social channels.</div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $goldBenefits = [
                        'Logo on all event pages',
                        '2 social posts per major event',
                        'LAN event branding (banner + table)',
                        'Hardware in prize pools',
                        'Semi-annual audience report',
                    ];
                    foreach ($goldBenefits as $b):
                    ?>
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-primary);">
                        <span style="color:var(--accent-blue);margin-top:1px;flex-shrink:0;">✓</span>
                        <?= htmlspecialchars($b, ENT_QUOTES) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Silver Partner -->
            <div style="background:var(--bg-secondary);border:1px solid rgba(192,192,192,0.3);border-radius:var(--radius-lg);padding:28px;">
                <div style="font-size:28px;margin-bottom:12px;">🥈</div>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:#C0C0C0;margin-bottom:6px;">Silver Partner</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;line-height:1.5;">Steady digital presence and community recognition.</div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $silverBenefits = [
                        'Logo on partners page & leaderboard',
                        '1 social post per season',
                        'Platform news feature',
                        'Annual community shoutout',
                    ];
                    foreach ($silverBenefits as $b):
                    ?>
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-primary);">
                        <span style="color:#C0C0C0;margin-top:1px;flex-shrink:0;">✓</span>
                        <?= htmlspecialchars($b, ENT_QUOTES) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Community Partner -->
            <div style="background:var(--bg-secondary);border:1px solid rgba(205,127,50,0.3);border-radius:var(--radius-lg);padding:28px;">
                <div style="font-size:28px;margin-bottom:12px;">🤝</div>
                <div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:#CD7F32;margin-bottom:6px;">Community Partner</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;line-height:1.5;">In-kind contributions — peripherals, prizes, merchandise.</div>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $communityBenefits = [
                        'Logo on partners page',
                        'Products in tournament prize pools',
                        'Mention in tournament news posts',
                        'Community Discord shoutout',
                    ];
                    foreach ($communityBenefits as $b):
                    ?>
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-primary);">
                        <span style="color:#CD7F32;margin-top:1px;flex-shrink:0;">✓</span>
                        <?= htmlspecialchars($b, ENT_QUOTES) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
    </div>

    <!-- Current Partners -->
    <div class="reveal" style="margin-bottom:80px;">
        <span class="section-label"><?= t('sponsors_current_label') ?></span>
        <h2 style="font-size:22px;font-weight:700;letter-spacing:-0.5px;margin-bottom:24px;"><?= t('sponsors_current_title') ?></h2>

        <div style="background:linear-gradient(135deg,rgba(0,212,255,0.03),rgba(102,126,234,0.03));border:2px dashed rgba(0,212,255,0.2);border-radius:var(--radius-lg);padding:56px;text-align:center;">
            <div style="font-size:48px;margin-bottom:16px;">🐉</div>
            <div style="font-family:var(--font-display);font-size:20px;font-weight:800;margin-bottom:10px;color:var(--text-primary);">
                <?= t('sponsors_be_first') ?>
            </div>
            <p style="color:var(--text-secondary);max-width:480px;margin:0 auto 24px;line-height:1.65;">
                <?= t('sponsors_be_first_sub') ?>
            </p>
            <a href="#inquiry" class="btn-primary" style="padding:12px 28px;"><?= t('sponsors_form_send') ?> →</a>
        </div>
    </div>

    <!-- Contact / Inquiry Form -->
    <div class="reveal form-card" id="inquiry" style="max-width:680px;margin:0 auto 80px;">
        <span class="section-label"><?= t('sponsors_cta_label') ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-0.8px;margin-bottom:10px;">
            <?= t('sponsors_cta_title') ?>
        </h2>
        <p style="color:var(--text-secondary);font-size:15px;margin-bottom:32px;line-height:1.65;">
            <?= t('sponsors_cta_sub') ?>
        </p>

        <?php if ($sent): ?>
        <div style="background:rgba(0,232,120,0.08);border:1px solid rgba(0,232,120,0.3);border-radius:var(--radius);padding:20px 24px;font-size:15px;font-weight:600;color:var(--success);text-align:center;margin-bottom:24px;">
            <?= t('sponsors_form_ok') ?>
        </div>
        <?php else: ?>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label"><?= t('sponsors_form_company') ?></label>
                    <input class="form-input" type="text" name="company" placeholder="MSI, ASUS, …"
                           value="<?= htmlspecialchars($_POST['company'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t('sponsors_form_name') ?></label>
                    <input class="form-input" type="text" name="contact_name" placeholder="Mario Rossi"
                           value="<?= htmlspecialchars($_POST['contact_name'] ?? '', ENT_QUOTES) ?>" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label"><?= t('sponsors_form_email') ?></label>
                    <input class="form-input" type="email" name="contact_email" placeholder="partnerships@brand.com"
                           value="<?= htmlspecialchars($_POST['contact_email'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t('sponsors_form_tier') ?></label>
                    <select class="form-input" name="tier" style="background:var(--bg-primary);color:var(--text-primary);">
                        <option value=""><?= t('sponsors_form_tier_opt') ?></option>
                        <option value="Title Sponsor" <?= (($_POST['tier'] ?? '') === 'Title Sponsor') ? 'selected' : '' ?>>🏆 Title Sponsor</option>
                        <option value="Gold Partner"  <?= (($_POST['tier'] ?? '') === 'Gold Partner')  ? 'selected' : '' ?>>🥇 Gold Partner</option>
                        <option value="Silver Partner"<?= (($_POST['tier'] ?? '') === 'Silver Partner')? 'selected' : '' ?>>🥈 Silver Partner</option>
                        <option value="Community Partner"<?= (($_POST['tier'] ?? '') === 'Community Partner') ? 'selected' : '' ?>>🤝 Community Partner</option>
                        <option value="Custom"        <?= (($_POST['tier'] ?? '') === 'Custom')        ? 'selected' : '' ?>>💡 Custom</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= t('sponsors_form_message') ?></label>
                <textarea class="form-textarea" name="message" rows="5"
                          placeholder="<?= htmlspecialchars(t('sponsors_form_msg_ph'), ENT_QUOTES) ?>"
                          required><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES) ?></textarea>
            </div>
            <button type="submit" class="btn-primary btn-submit"><?= t('sponsors_form_send') ?> →</button>
        </form>

        <?php endif; ?>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../templates/layout/footer.php'; ?>
