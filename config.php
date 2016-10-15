<?php

return [
  'temp_path' => './temp/',
  'printer' => [
    'options' => getenv('PRINT_OPTIONS'),
    /**
     * Most likely, the web user will not be allowed to execute the lprm command.
     * This requires us to pass in the -U flag with a username that can remove
     * print jobs from the queue.
     */
    'flush_jobs' => (bool) getenv('PRINT_FLUSH_JOBS'),
    'username' => getenv('PRINT_FLUSH_JOBS_USERNAME') ? getenv('PRINT_FLUSH_JOBS_USERNAME') : '`whoami`',
  ],
];
