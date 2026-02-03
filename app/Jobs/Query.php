<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class Query implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobExecutionId;

    public $database;

    public $params;

    public $hash;

    /**
     * Create a new job instance.
     */
    public function __construct(int $jobExecutionId, array $database, string $params)
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

        // Wait
        // usleep(3000);

        // Run Query In Tenant
        $conn = DB::connection('tenant'.$this->hash);

        // Disable SQL_SAFE_UPDATES and FOREIGN_KEY_CHECKS
        $conn->statement('SET SQL_SAFE_UPDATES = 0');
        $conn->statement('SET FOREIGN_KEY_CHECKS = 0');

        // Execute Query
        \Log::debug($this->params);
        $statement = $conn->getPdo()->prepare($this->params);
        $statement->execute();

        // Enable SQL_SAFE_UPDATES and FOREIGN_KEY_CHECKS
        $conn->statement('SET SQL_SAFE_UPDATES = 1');
        $conn->statement('SET FOREIGN_KEY_CHECKS = 1');

        // Get the SQL type (SELECT, INSERT, UPDATE OR DELETE)
        $sqlType = strtoupper(strtok(trim($this->params), ' '));

        switch ($sqlType) {
            case 'SELECT':
                return json_encode($statement->fetchAll(\PDO::FETCH_ASSOC));

            case 'UPDATE':
            case 'DELETE':
                return 'The operation affected '.(int) $statement->rowCount().' records';

            case 'INSERT':
                return 'Inserted record with ID: '.$conn->getPdo()->lastInsertId();

            default:
                return 'Query executed successfully.';
        }
    }
}
