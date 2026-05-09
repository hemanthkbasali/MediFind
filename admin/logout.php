<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

logout_session();
set_flash('success', 'Admin session ended.');
redirect('index.php');
