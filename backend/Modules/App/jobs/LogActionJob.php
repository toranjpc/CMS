<?php

namespace Modules\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\App\Models\Log;

class LogActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $tableName;
    public $tableId;
    public $type;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $tableName, $tableId, $type, $data = null)
    {
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->tableId = $tableId;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::create([
            'user_id' => $this->userId,
            'table_name' => $this->tableName,
            'table_id' => $this->tableId,
            'type' => $this->type,
            'data' => is_array($this->data) ? json_encode($this->data) : $this->data,
        ]);
    }
}
