<?php
require_once '../vendor/autoload.php';
use League\Plates\Engine;

// Initialize Plates engine
$templates = new Engine(__DIR__ . '/../templates');

// Simple render function
function renderWithLayout($content, $data = []) {
    global $templates;
    return $templates->render('layout', array_merge($data, ['content' => $content]));
}