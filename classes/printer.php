<?php

class Printer
{
    public function __construct(\Slim\Slim $app, $queue)
    {
        $this->config = $app->config('printer');
        $this->app = $app;
        $this->queue = escapeshellarg($queue);
        $this->printCmd = "lpr -P $this->queue {$this->config['options']}";
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

        if ($this->config['flush_jobs']) {
            // To keep duplicate jobs from stacking in case the printer
            // connection gets lost, flush all jobs before sending a new one.
            exec("lprm -U {$this->config['username']} -P $this->queue -");
        }

        if (isset($body->base64) && $body->base64 === true) {
            // Handle base64 encoded print data.
            try {
                // Save the file.
                $file = $app->config('temp_path') . time();
                file_put_contents($file, base64_decode($body->data));
                // Print the file.
                exec("$this->printCmd $file");
                // Delete the file.
                unlink($file);
            }
            catch (\ErrorException $e) {
                return $e->getMessage();
            }
        }
        else {
            // Send raw data straight to the printer.
            try {
                // To prevent injection, strip out any insance of the
                // closing heredoc.
                $body->data = preg_replace('/\n\b(STDIN)\b\n/', '', $body->data);
                // Passing the $body->data into the standard input with
                // a heredoc keeps us from dealing with escaping quotes and
                // other characters.
                exec("$this->printCmd <<'STDIN'\n$body->data\nSTDIN");
            }
            catch (\ErrorException $e) {
                return $e->getMessage();
            }
        }
    }
}
