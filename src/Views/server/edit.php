<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Edit Server</h1>

<p><a href="/server?server=<?= urlencode($server['address']) ?>">← Back to server</a></p>

<h2><?= e($server['address']) ?></h2>

<form method="POST" action="/server/edit" class="form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <input type="hidden" name="server" value="<?= e($server['address']) ?>">

    <div class="form-group">
        <label for="name">Server Name</label>
        <input
            type="text"
            name="name"
            id="name"
            value="<?= e(old('name', $server['name'])) ?>"
            maxlength="100"
            placeholder="My Awesome Screeps Server"
        >
        <?php if (isset($errors['name'])): ?>
            <span class="error"><?= e($errors['name']) ?></span>
        <?php endif; ?>
        <small>Display name for your server (max 100 characters)</small>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea
            name="description"
            id="description"
            rows="6"
            maxlength="1000"
            placeholder="Describe your server: mods, rules, special features, etc."
        ><?= e(old('description', $server['description'])) ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <span class="error"><?= e($errors['description']) ?></span>
        <?php endif; ?>
        <small>Tell players about your server (max 1000 characters)</small>
    </div>

    <button type="submit" class="button">Save Changes</button>
</form>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
