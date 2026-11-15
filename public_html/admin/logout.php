<?php
require __DIR__ . '/../../private_app/bootstrap.php';
$_SESSION = [];
session_destroy();
redirect('login.php');
