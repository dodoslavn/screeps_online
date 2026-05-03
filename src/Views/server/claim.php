<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Claim Server</h1>

<p><a href="/server?server=<?= urlencode($server['address']) ?>">← Back to server</a></p>

<h2><?= e($server['name'] ?: $server['address']) ?></h2>

<div class="info-box">
    <h3>How to claim this server:</h3>
    <ol>
        <li>Copy the claim token below</li>
        <li>Add it to your server's <strong>welcomeText</strong> configuration</li>
        <li>Restart your server or wait for configuration reload</li>
        <li>Click the "Verify and Claim" button</li>
    </ol>
</div>

<div class="claim-token">
    <h3>Your Claim Token:</h3>
    <code class="token"><?= e($claim_token) ?></code>
    <p><small>Add this token anywhere in your server's welcomeText. You can remove it after claiming.</small></p>
</div>

<form method="POST" action="/server/claim" class="form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <input type="hidden" name="server" value="<?= e($server['address']) ?>">

    <button type="submit" class="button">Verify and Claim Server</button>
</form>

<div class="info-box">
    <h3>Note:</h3>
    <p>Maximum 10 claim attempts per session. Make sure the token is correctly added to your server before attempting verification.</p>
    <p>Attempts remaining: <?= 10 - ($_SESSION['claim_attempts'] ?? 0) ?></p>
</div>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
