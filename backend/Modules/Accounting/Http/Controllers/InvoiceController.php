<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\Invoice;
use Modules\Accounting\Models\InvoiceItem;
use Modules\Accounting\Models\Product;
use Modules\Accounting\Models\ProductItem;
use Modules\User\Models\User;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['user', 'party'])
            ->select("*");

        if (!empty(request('trashed')) && request('trashed') == "true") {
            $invoices = $invoices->onlyTrashed();
        }

        if (!empty(request('type'))) {
            $invoices = $invoices->where('type', request('type'));
        }

        if (!empty(request('status'))) {
            $invoices = $invoices->where('status', request('status'));
        }

        if (!empty(request('party_id'))) {
            $invoices = $invoices->where('party_id', request('party_id'));
        }

        if (!empty(request('date_from'))) {
            $invoices = $invoices->where('date', '>=', request('date_from'));
        }

        if (!empty(request('date_to'))) {
            $invoices = $invoices->where('date', '<=', request('date_to'));
        }

        if (!empty(request('invoice_number'))) {
            $invoices = $invoices->where('invoice_number', 'LIKE', '%' . request('invoice_number') . '%');
        }

        $invoices = $invoices->orderBy('id', 'DESC')->paginate(request("limit", 10));

        $result = [
            'items' => $invoices->items(),
            'total' => $invoices->total(),
            'per_page' => $invoices->perPage(),
            'current_page' => $invoices->currentPage(),
            'last_page' => $invoices->lastPage(),
            'from' => $invoices->firstItem(),
            'to' => $invoices->lastItem(),
        ];

        return response()->json([
            "status" => "success",
            "data" => $result
        ], 200);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['user', 'party', 'items.product', 'items.productItem', 'items.warehouse'])->withTrashed()->findOrFail($id);
        return response()->json([
            "status" => "success",
            "data" => $invoice
        ], 200);
    }

    public function showByNumber(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string'
        ]);

        $invoice = Invoice::with(['user', 'party', 'items.product', 'items.productItem', 'items.warehouse'])
            ->where('invoice_number', $request->invoice_number)
            ->firstOrFail();

        return response()->json([
            "status" => "success",
            "data" => $invoice
        ], 200);
    }

    public function lastid(Request $request)
    {
        $type = $request->get('type', 'buy');

        // تاریخ شمسی فرمت: سال2رقمی + ماه2رقمی + روز2رقمی
        $date = date('ymd'); // 241015 برای مثال

        $lastInvoice = Invoice::where('type', $type)
            ->where('invoice_number', 'LIKE', ($type === 'buy' ? 'B' : 'S') . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        $invoiceNumber = ($type === 'buy' ? 'B' : 'S') . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return response()->json([
            "status" => "success",
            "data" => $invoiceNumber
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            // Log request data for debugging
            Log::info('Invoice store request', [
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $data = $request->validate([
                'type' => 'required|in:buy,sell',
                'party_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'subtotal' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'status' => 'nullable|in:draft,confirmed,paid,cancelled',
                'description' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_item_id' => 'nullable|integer',
                'items.*.warehouse_id' => [
                    'required',
                    'integer',
                    Rule::exists('product_options', 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
                    }),
                ],
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.total_price' => 'required|numeric|min:0',
                'items.*.description' => 'nullable|string',
            ]);

            // Additional validation for product_item_id if provided
            foreach ($data['items'] as $key => $item) {
                if (!empty($item['product_item_id'])) {
                    $productItem = \Modules\Accounting\Models\ProductItem::find($item['product_item_id']);
                    if (!$productItem) {
                        throw ValidationException::withMessages([
                            "items.{$key}.product_item_id" => 'تنوع محصول انتخاب شده یافت نشد.'
                        ]);
                    }
                }
            }

            $this->validateInvoiceStock($data['type'], $data['items']);

            // Generate invoice number
            $date = date('ymd'); // تاریخ شمسی فرمت
            $typePrefix = $data['type'] === 'buy' ? 'B' : 'S';

            $lastInvoice = Invoice::where('type', $data['type'])
                ->where('invoice_number', 'LIKE', $typePrefix . $date . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;
            $invoiceNumber = $typePrefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $userId = data_get($request->user(), 'id');

            if (!$userId) {
                throw ValidationException::withMessages([
                    'user' => 'کاربر احراز هویت نشده است. لطفا دوباره وارد شوید.',
                ]);
            }

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $userId,
                'type' => $data['type'],
                'party_id' => $data['party_id'],
                'date' => $data['date'],
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'total' => $data['total'],
                'status' => $data['status'] ?? 'draft',
                'description' => $data['description'] ?? null
            ]);

            // Create invoice items
            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'product_item_id' => $item['product_item_id'] ?? null,
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'description' => $item['description'] ?? null,
                    'type' => $data['type'],
                ]);
            }

            return response()->json([
                "status" => "success",
                "data" => $invoice->load(['items.product', 'items.productItem', 'items.warehouse']),
                "message" => "فاکتور با موفقیت ایجاد شد"
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Invoice validation error', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Invoice store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ثبت فاکتور رخ داد: " . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Log request data for debugging
            Log::info('Invoice update request', [
                'id' => $id,
                'data' => $request->all()
            ]);

            $data = $request->validate([
                'party_id' => 'sometimes|exists:users,id',
                'date' => 'sometimes|date',
                'subtotal' => 'sometimes|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'total' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:draft,confirmed,paid,cancelled',
                'description' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.product_item_id' => 'nullable|integer',
                'items.*.warehouse_id' => [
                    'required',
                    'integer',
                    Rule::exists('product_options', 'id')->where(function ($query) {
                        $query->where('kind', 'warehouse');
                    }),
                ],
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.total_price' => 'required|numeric|min:0',
                'items.*.description' => 'nullable|string',
            ]);

            // Additional validation for product_item_id if provided
            if (isset($data['items'])) {
                foreach ($data['items'] as $key => $item) {
                    if (!empty($item['product_item_id'])) {
                        $productItem = \Modules\Accounting\Models\ProductItem::find($item['product_item_id']);
                        if (!$productItem) {
                            throw ValidationException::withMessages([
                                "items.{$key}.product_item_id" => 'تنوع محصول انتخاب شده یافت نشد.'
                            ]);
                        }
                    }
                }
            }

            $invoice = Invoice::findOrFail($id);
            $targetType = $data['type'] ?? $invoice->type;

            if (isset($data['items'])) {
                $this->validateInvoiceStock($targetType, $data['items'], (int) $invoice->id);
            }

            // Update invoice
            $invoice->update(collect($data)->except('items')->toArray());

            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $invoice->items()->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'product_item_id' => $item['product_item_id'] ?? null,
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                        'description' => $item['description'] ?? null,
                        'type' => $invoice->type,
                    ]);
                }
            }

            return response()->json([
                "status" => "success",
                "data" => $invoice->load(['items.product', 'items.productItem', 'items.warehouse'])
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Invoice update validation error', [
                'id' => $id,
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                "status" => "validation_error",
                "errors" => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Invoice update error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                "status" => "error",
                "message" => "خطایی در ویرایش فاکتور رخ داد: " . $e->getMessage(),
            ], 500);
        }
    }

    private function validateInvoiceStock(string $invoiceType, array $items, ?int $ignoreInvoiceId = null): void
    {
        // فقط برای فاکتور فروش موجودی کنترل می‌شود.
        if ($invoiceType !== 'sell' || empty($items)) {
            return;
        }

        $productItemIds = [];
        $requestedRows = [];
        foreach ($items as $index => $item) {
            $productItemId = (int) ($item['product_item_id'] ?? 0);
            $requestedQty = (int) ($item['quantity'] ?? 0);
            if (!$productItemId || $requestedQty <= 0) {
                continue;
            }

            $productItemIds[] = $productItemId;
            $requestedRows[] = [
                'index' => $index,
                'product_item_id' => $productItemId,
                'quantity' => $requestedQty,
            ];
        }
        $productItemIds = array_values(array_unique($productItemIds));

        if (empty($productItemIds)) {
            return;
        }

        $productItems = ProductItem::whereIn('id', $productItemIds)
            ->get(['id', 'title', 'firstWarehouse'])
            ->keyBy('id');

        $sellRows = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereIn('invoice_items.product_item_id', $productItemIds)
            ->where('invoices.type', 'sell')
            ->whereNull('invoices.deleted_at')
            ->when($ignoreInvoiceId, fn($q) => $q->where('invoices.id', '!=', $ignoreInvoiceId))
            ->selectRaw('product_item_id, COALESCE(SUM(quantity), 0) as total_qty')
            ->groupBy('product_item_id')
            ->pluck('total_qty', 'product_item_id');

        $buyRows = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereIn('invoice_items.product_item_id', $productItemIds)
            ->where('invoices.type', 'buy')
            ->whereNull('invoices.deleted_at')
            ->when($ignoreInvoiceId, fn($q) => $q->where('invoices.id', '!=', $ignoreInvoiceId))
            ->selectRaw('product_item_id, COALESCE(SUM(quantity), 0) as total_qty')
            ->groupBy('product_item_id')
            ->pluck('total_qty', 'product_item_id');

        // برای جلوگیری از عبور مجموع چند ردیف یک محصول در همان درخواست
        $remainingByProductItem = [];

        foreach ($requestedRows as $row) {
            $index = $row['index'];
            $productItemId = $row['product_item_id'];
            $requestedQty = $row['quantity'];

            $productItem = $productItems->get($productItemId);
            if (!$productItem) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_item_id" => "تنوع محصول با شناسه {$productItemId} یافت نشد.",
                ]);
            }

            if (!array_key_exists($productItemId, $remainingByProductItem)) {
                $baseStock = (int) ($productItem->firstWarehouse ?? 0);
                $buyQty = (int) ($buyRows[$productItemId] ?? 0);
                $sellQty = (int) ($sellRows[$productItemId] ?? 0);
                $remainingByProductItem[$productItemId] = $baseStock + $buyQty - $sellQty;
            }

            if ($requestedQty > $remainingByProductItem[$productItemId]) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" =>
                        "موجودی {$productItem->title} کافی نیست. موجودی قابل ثبت: {$remainingByProductItem[$productItemId]} | مقدار درخواستی: {$requestedQty}",
                ]);
            }

            $remainingByProductItem[$productItemId] -= $requestedQty;
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json([
            "status" => "success",
            "message" => "فاکتور با موفقیت حذف شد"
        ], 200);
    }

    public function force_destroy($id)
    {
        $invoice = Invoice::withTrashed()->findOrFail($id);
        $invoice->forceDelete();
        return response()->json([
            "status" => "success",
            "message" => "فاکتور به صورت دائمی حذف شد"
        ], 200);
    }

    public function restore($id)
    {
        $invoice = Invoice::withTrashed()->findOrFail($id);
        $invoice->restore();
        return response()->json([
            "status" => "success",
            "message" => "فاکتور بازیابی شد",
            "data" => $invoice
        ], 200);
    }
}
