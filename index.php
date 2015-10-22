<?php

require 'vendor/autoload.php';
require 'classes/printer.php';

$app = new \Slim\Slim();
$app->add(new \CorsSlim\CorsSlim([
    'origin' => '*',
    'allowMethods' => 'POST',
]));

// Legacy endpoint (deprecated).
$app->post('/printers/:queue/jobs', function($queue) {
    \Printer::printJob($queue);
});

// That new hotness.
$app->post('/print/:queue', function($queue) {
    \Printer::printJob($queue);
});

$app->run();
