<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>My Account</h1>

<p><a href="/">← Back to home</a></p>

<h2>Account Information</h2>

<table class="account-info">
    <tr>
        <th>Username</th>
        <td><?= e($user['name']) ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= e($user['email']) ?></td>
    </tr>
    <tr>
        <th>Member Since</th>
        <td><?= isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'Unknown' ?></td>
    </tr>
</table>

<h2>My Servers</h2>

<?php if (empty($owned_servers)): ?>
    <p>You haven't claimed any servers yet. <a href="/">Browse servers</a> to claim one.</p>
<?php else: ?>
    <table class="server_list">
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Status</th>
            <th>Players</th>
            <th>Availability</th>
            <th></th>
        </tr>
        <?php foreach ($owned_servers as $server): ?>
            <?php $color = ($server['online'] == 1) ? 'green' : 'red'; ?>
            <tr>
                <td><?= e($server['name'] ?: $server['address']) ?></td>
                <td><?= e($server['address']) ?></td>
                <td style="background-color: <?= $color ?>"></td>
                <td><?= (int)$server['players_current'] ?></td>
                <td><?= number_format($server['availability'] ?? 0, 2) ?>%</td>
                <td>
                    <a href="/server?server=<?= urlencode($server['address']) ?>">View</a> |
                    <a href="/server/edit?server=<?= urlencode($server['address']) ?>">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p>
    <a href="/account/logout" class="button">Logout</a>
</p>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
