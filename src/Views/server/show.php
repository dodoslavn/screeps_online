<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>Server Information</h1>

<p><a href="/">← Back to server list</a></p>

<table class="server_detail">
    <tr>
        <th>Server Address</th>
        <td><?= e($host) ?>:<?= e($port) ?></td>
    </tr>
    <tr>
        <th>Name</th>
        <td><?= e($server['name'] ?: 'Not set') ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td>
            <?php if ($server['online'] == 1): ?>
                <span style="color: green;">● Online</span>
            <?php else: ?>
                <span style="color: red;">● Offline</span>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Current Players</th>
        <td><?= (int)$server['players_current'] ?></td>
    </tr>
    <tr>
        <th>Version</th>
        <td><?= e($server['version'] ?: 'Unknown') ?></td>
    </tr>
    <tr>
        <th>Last Check</th>
        <td><?= $server['last_check'] ? date('H:i:s d.m.Y', strtotime($server['last_check'])) : 'Never' ?></td>
    </tr>
    <tr>
        <th>Availability</th>
        <td><?= number_format($server['availability'] ?? 0, 2) ?>%</td>
    </tr>
    <tr>
        <th>Owner</th>
        <td>
            <?php if ($server['owner_name']): ?>
                <?= e($server['owner_name']) ?>
            <?php else: ?>
                Unclaimed
                <?php if ($is_authenticated): ?>
                    - <a href="/server/claim?server=<?= urlencode($server['address']) ?>">Claim this server</a>
                <?php else: ?>
                    - <a href="/account/login">Login to claim</a>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php if ($server['description']): ?>
        <tr>
            <th>Description</th>
            <td><?= nl2br(e($server['description'])) ?></td>
        </tr>
    <?php endif; ?>
</table>

<?php if ($is_authenticated && $current_user && $server['user_id'] == $current_user['id_user']): ?>
    <p>
        <a href="/server/edit?server=<?= urlencode($server['address']) ?>" class="button">Edit Server</a>
    </p>
<?php endif; ?>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
