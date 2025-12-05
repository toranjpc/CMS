<?php

use Modules\App\Models\App;
use Modules\App\jobs\LogActionJob;

class AppObserver
{
    public function created(App $App)
    {
        $this->log('create', $App);
    }

    public function updated(App $App)
    {
        $this->log('update', $App);
    }

    public function deleting(App $App)
    {
        $this->log('delete', $App); //قبل از حذف دیتا در لاگ ذخیره شود
    }
    public function deleted(App $App)
    {
        // $this->log('delete', $App);
    }

    protected function log($type, $model)
    {
        $changes = $model->getChanges();
        if ($type === 'update') {
            if (empty($changes)) return;

        }

        $old = $type === 'create' ? null : $model->getOriginal();
        $new = $model->getChanges();

        LogActionJob::dispatch(
            auth()->id() ?? null,
            $model->getTable(),
            $model->id,
            $type,
            [
                'old' => $old,
                'new' => $new
            ]
        );
    }
}
