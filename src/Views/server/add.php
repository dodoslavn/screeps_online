<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Add Server</h1>

<p><a href="/">← Back to server list</a></p>

<form method="POST" action="/add" class="form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <div class="form-group">
        <label for="server">Server Address (host:port)</label>
        <input
            type="text"
            name="server"
            id="server"
            value="<?= e(old('server')) ?>"
            placeholder="example.com:21025"
            required
        >
        <?php if (isset($errors['server'])): ?>
            <span class="error"><?= e($errors['server']) ?></span>
        <?php endif; ?>
        <small>Enter the server address in format: hostname:port or ip:port (e.g., server.example.com:21025)</small>
    </div>

    <button type="submit" class="button">Add Server</button>
</form>

<div class="info-box">
    <h3>Requirements</h3>
    <ul>
        <li>Server must be publicly accessible</li>
        <li>Port must be between 1024 and 65535</li>
        <li>Server must respond to /api/version/ endpoint</li>
        <li>Servers offline for more than 3 days will be hidden from the main list</li>
    </ul>
</div>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
