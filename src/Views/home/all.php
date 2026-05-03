<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>All Servers</h1>

<p><a href="/">← Back to recent servers</a></p>

<table class="server_list">
    <tr>
        <th>Name</th>
        <th colspan="2">Address</th>
        <th>Online</th>
        <th>Players</th>
        <th>Version</th>
        <th>Last check/online</th>
        <th>Availability</th>
        <th></th>
    </tr>
    <?php if (empty($servers)): ?>
        <tr>
            <td colspan="9" style="text-align: center; padding: 20px;">
                No servers found.
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($servers as $server): ?>
            <?php
            $color = ($server['online'] == 1) ? 'green' : 'red';
            $parts = explode(':', $server['address']);
            $host = $parts[0] ?? '';
            $port = $parts[1] ?? '';
            $lastCheck = $server['last_check'] ? date('H:i:s d.m.Y', strtotime($server['last_check'])) : 'Never';
            $availability = $server['availability'] ?? 0;
            ?>
            <tr>
                <td><?= e($server['name'] ?: $server['address']) ?></td>
                <td><?= e($host) ?></td>
                <td><?= e($port) ?></td>
                <td style="background-color: <?= $color ?>"></td>
                <td><?= (int)$server['players_current'] ?></td>
                <td><?= e($server['version']) ?></td>
                <td><?= e($lastCheck) ?></td>
                <td><?= number_format($availability, 2) ?>%</td>
                <td><a class="server" href="/server?server=<?= urlencode($server['address']) ?>">Info</a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
