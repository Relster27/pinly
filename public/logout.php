<?php
require __DIR__ . '/../includes/bootstrap.php';

$_SESSION = [];
session_unset();
session_destroy();

redirect('login.php');
