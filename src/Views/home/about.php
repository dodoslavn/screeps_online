<?php require __DIR__ . '/../layouts/main.php'; ?>

<h1>About this web page</h1>

<h3>Why?</h3>
<p>Well, in time when this project was created, game Screeps had list of private servers in main menu of game.
    However, those were added by developers manually, and server owners didn't have much control of it.
    For example, you can't even define the name of your server, which will be used on the list.</p>
<p>There is also forum, where owners can add their own server and share some details, but most of the servers are dead / not working.</p>

<h3>Who?</h3>
<p>I am just owner of few Screeps (and other) servers, sometimes I do some webpage, and I think this project will help community and game overall.
    Players who can't play on Official server anymore, can still experiment on those private / community servers, and eventually come back to the "Master - official" server.</p>

<h3>How?</h3>
<p>The way this page works: it is doing simple HTTP requests via PHP to the API of Screeps, every minute. If server is added and online, it will appear on server list.
    If server is offline for more than 3 days, it will disappear, however it will stay in database and will be scanned. So once it comes online, it will appear in list again automatically.</p>

<h3>Features</h3>
<p>You can now log in to this page and claim your server! Once claimed, you can edit server name and description.
    To claim a server, you need to add a verification token to your server's welcomeText and we'll verify you own it.</p>

<h4>screeps.online</h4>
Version: 2.0 (Security Update)<br>
Creator: Dodoslav Novak<br>
Contact: screeps@fordo.sk<br>

<h4>Screeps</h4>
Web: <a href="https://screeps.com/" target="_blank">https://screeps.com/</a><br>
Steam: <a href="https://store.steampowered.com/app/464350/Screeps/" target="_blank">https://store.steampowered.com/app/464350/Screeps/</a><br>
Slack: <a href="https://chat.screeps.com/" target="_blank">https://chat.screeps.com/</a><br>
Forum: <a href="https://screeps.com/forum/" target="_blank">https://screeps.com/forum/</a>

<div class="footer">
    Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019-<?= date('Y') ?> | Timezone <?= e(date_default_timezone_get()) ?>
</div>

</div>
</body>
</html>
