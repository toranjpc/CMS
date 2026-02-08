<?php

namespace Modules\Accounting\Console\Commands;

use Illuminate\Console\Command;
use Modules\Accounting\Models\Product;

class UpdateProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:update-product-stock {--force : Force update all products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update current_stock field for all products based on invoice items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating product stock...');

        $products = Product::all();
        $bar = $this->output->createProgressBar($products->count());

        $bar->start();

        foreach ($products as $product) {
            $realStock = $product->real_stock;
            $product->update(['current_stock' => $realStock]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Product stock updated successfully!');
    }
}
