<?php
session_start();

// Load router and handle request
$router = require_once 'config/routes.php';
$router->handleRequest();
?>