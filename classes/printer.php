<?php

class Printer
{
    public function __construct(\Slim\Slim $app, $queue)
    {
        $this->app = $app;
        $this->queue = $queue;
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
                $body = json_decode($app->request->getBody());
                break;

            default:
                $app->response->setStatus(400);
                return;
                break;
        }
        exec("echo '$body->data' | lpr -P $this->queue");
    }
}
