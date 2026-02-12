<?php

namespace Modules\Accounting\Observers;

use Modules\Accounting\Models\InvoiceItem;

class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     */
    public function created(InvoiceItem $invoiceItem): void
    {
        $this->updateProductStock($invoiceItem);
    }

    /**
     * Handle the InvoiceItem "updated" event.
     */
    public function updated(InvoiceItem $invoiceItem): void
    {
        // Only update if quantity or type changed
        if ($invoiceItem->wasChanged(['quantity', 'type'])) {
            $this->updateProductStock($invoiceItem);
        }
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     */
    public function deleted(InvoiceItem $invoiceItem): void
    {
        $this->updateProductStockOnDelete($invoiceItem);
    }

    /**
     * Handle the InvoiceItem "restored" event.
     */
    public function restored(InvoiceItem $invoiceItem): void
    {
        $this->updateProductStock($invoiceItem);
    }

    /**
     * Handle the InvoiceItem "force deleted" event.
     */
    public function forceDeleted(InvoiceItem $invoiceItem): void
    {
        $this->updateProductStockOnDelete($invoiceItem);
    }

    private function updateProductStock(InvoiceItem $invoiceItem): void
    {
        $productItem = $invoiceItem->productItem;
        if (!$productItem) return;

        $quantity = $invoiceItem->quantity;

        if ($invoiceItem->type === 'buy') {
            $productItem->increment('current_stock', $quantity);
        } elseif ($invoiceItem->type === 'sell') {
            $productItem->decrement('current_stock', $quantity);
        }
    }

    private function updateProductStockOnDelete(InvoiceItem $invoiceItem): void
    {
        $productItem = $invoiceItem->productItem;
        if (!$productItem) return;

        $quantity = $invoiceItem->quantity;

        // Reverse the operation
        if ($invoiceItem->type === 'buy') {
            $productItem->decrement('current_stock', $quantity);
        } elseif ($invoiceItem->type === 'sell') {
            $productItem->increment('current_stock', $quantity);
        }
    }
}
