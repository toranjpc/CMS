<?php

use Modules\Product\Models\Product;
use Modules\App\jobs\LogActionJob;

class ProductObserver
{
    public function created(Product $Product)
    {
        $this->log('create', $Product);
    }

    public function updated(Product $Product)
    {
        $this->log('update', $Product);
    }

    public function deleting(Product $Product)
    {
        $this->log('delete', $Product); //قبل از حذف دیتا در لاگ ذخیره شود
    }
    public function deleted(Product $Product)
    {
        // $this->log('delete', $Product);
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
