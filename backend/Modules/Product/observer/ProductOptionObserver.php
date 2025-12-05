<?php

use Modules\Product\Models\ProductOption;
use Modules\App\jobs\LogActionJob;

class ProductOptionObserver
{
    public function created(ProductOption $ProductOption)
    {
        $this->log('create', $ProductOption);
    }

    public function updated(ProductOption $ProductOption)
    {
        $this->log('update', $ProductOption);
    }

    public function deleting(ProductOption $ProductOption)
    {
        $this->log('delete', $ProductOption); //قبل از حذف دیتا در لاگ ذخیره شود
    }
    public function deleted(ProductOption $ProductOption)
    {
        // $this->log('delete', $ProductOption);
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
