<?php

namespace Modules\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\App\Models\Log;
use Modules\User\Models\User;

class LogActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $tableName;
    public $tableId;
    public $type;
    public $data;
    public $appId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $tableName, $tableId, $type, $data = null, $appId = null)
    {
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->tableId = $tableId;
        $this->type = $type;
        $this->data = $data;
        $this->appId = $appId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // اگر app_id داده نشده، سعی می‌کنیم از user بگیریم
        $appId = $this->appId;
        
        if ($appId === null && $this->userId) {
            $user = User::find($this->userId);
            if ($user && $user->app_id) {
                $appId = $user->app_id;
            }
        }

        Log::create([
            'user_id' => $this->userId,
            'table_name' => $this->tableName,
            'table_id' => $this->tableId,
            'type' => $this->type,
            'data' => is_array($this->data) ? json_encode($this->data) : $this->data,
            'app_id' => $appId,
        ]);
    }
}
