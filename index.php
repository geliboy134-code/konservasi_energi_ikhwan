<?php
require __DIR__ . '/includes/init.php';
redirect(is_login() ? 'dashboard.php' : 'login.php');
