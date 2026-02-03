<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Other implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobExecutionId;

    public $database;

    public $params;

    public $hash;

    /**
     * Create a new job instance.
     */
    public function __construct(int $jobExecutionId, array $database, ?string $params = null)
    {
        $this->hash = '_'.md5($database['db_database'].time());
        $this->jobExecutionId = $jobExecutionId;
        $this->database = $database;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(): string
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return '';
        }

        // My Custom Job
        // \Log::debug('My Custom Job');

        return 'Your Response';
    }
}
