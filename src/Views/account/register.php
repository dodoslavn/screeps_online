<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Create Account</h1>

<p><a href="/">← Back to home</a></p>

<form method="POST" action="/account/create" class="form">
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
            pattern="[a-zA-Z0-9_]+"
            minlength="3"
            maxlength="20"
        >
        <?php if (isset($errors['username'])): ?>
            <span class="error"><?= e($errors['username']) ?></span>
        <?php endif; ?>
        <small>3-20 characters, letters, numbers, and underscores only</small>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input
            type="email"
            name="email"
            id="email"
            value="<?= e(old('email')) ?>"
            required
        >
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
            type="password"
            name="password"
            id="password"
            required
            minlength="8"
        >
        <?php if (isset($errors['password'])): ?>
            <span class="error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
        <small>At least 8 characters with uppercase, lowercase, and number</small>
    </div>

    <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input
            type="password"
            name="password_confirm"
            id="password_confirm"
            required
        >
        <?php if (isset($errors['password_confirm'])): ?>
            <span class="error"><?= e($errors['password_confirm']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="button">Create Account</button>
</form>

<p>
    Already have an account? <a href="/account/login">Login</a>
</p>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
