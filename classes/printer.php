<?php

class Printer
{
    public function __construct(\Slim\Slim $app, $queue)
    {
        $this->config = $app->config('printer');
        $this->app = $app;
        $this->queue = escapeshellarg($queue);
    }

    public static function printJob($queue) {
        $app = \Slim\Slim::getInstance();
        $printer = new Printer($app, $queue);
        $printer->sendToPrinter();
    }

    public function sendToPrinter()
    {
        $app = $this->app;
        switch ($app->request->getContentType()) {
            case 'application/json':
                $app->response()->header('Content-Type', 'application/json');
                $body = json_decode($app->request->getBody())->data;
                break;

            default:
                $app->response->setStatus(400);
                return;
                break;
        }

        if ($this->config['flush_jobs']) {
            // To keep duplicate jobs from stacking in case the printer
            // connection gets lost, flush all jobs before sending a new one.
            exec("lprm -U {$this->config['username']} -P $this->queue -");
        }

        exec("echo '$body' | lpr -P $this->queue {$this->config['options']}");
    }
}
