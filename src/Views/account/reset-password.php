<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Reset Password</h1>

<p><a href="/account/login">← Back to login</a></p>

<p>Enter your email address and we'll send you instructions to reset your password.</p>

<form method="POST" action="/account/reset-password" class="form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <div class="form-group">
        <label for="email">Email Address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="<?= e(old('email')) ?>"
            required
            autofocus
        >
        <?php if (isset($errors['email'])): ?>
            <span class="error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="button">Send Reset Instructions</button>
</form>

<div class="info-box">
    <h3>Note:</h3>
    <p>If you have an old password from before the security update, you will need to reset it here.</p>
</div>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
