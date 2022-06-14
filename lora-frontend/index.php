<?php
    date_default_timezone_set('America/New_York');
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL); //-1

    $config = include_once 'config.php';

    require_once __DIR__ . '/vendor/autoload.php';

    session_start();

    require_once __DIR__ . '/routes.php';