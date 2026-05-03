<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Login</h1>

<p><a href="/">← Back to home</a></p>

<form method="POST" action="/account/login" class="form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input
            type="text"
            name="username"
            id="username"
            value="<?= e(old('username')) ?>"
            required
            autofocus
        >
        <?php if (isset($errors['username'])): ?>
            <span class="error"><?= e($errors['username']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
            type="password"
            name="password"
            id="password"
            required
        >
        <?php if (isset($errors['password'])): ?>
            <span class="error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="button">Login</button>
</form>

<p>
    Don't have an account? <a href="/account/create">Create one</a><br>
    Forgot your password? <a href="/account/reset-password">Reset it</a>
</p>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
