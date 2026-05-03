<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Screeps private community server list">
    <meta name="keywords" content="screeps private community server list game">
    <meta name="author" content="Dodoslav Novák">
    <link rel="stylesheet" type="text/css" href="<?= asset('css/default.css') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?>Screeps Private Server List</title>
</head>
<body>

<div class="header">
    <div class="head">
        <div class="logo"></div>
        <div class="panel">
            <a style="color: #ffe799;" href="/">Server list</a>
            <a href="/add">Add server</a>
            <a href="/about">About</a>
            <?php if ($is_authenticated): ?>
                <a href="/account">Account</a>
            <?php else: ?>
                <a href="/account/login">Log in</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <?php if (isset($success_message) && $success_message): ?>
        <div class="alert alert-success">
            <?= e($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message) && $error_message): ?>
        <div class="alert alert-error">
            <?= e($error_message) ?>
        </div>
    <?php endif; ?>

    <?php
    // This is where the page content will be inserted
    // Child views should define their content after including this layout
    ?>
