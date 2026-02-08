<template>
  <h2 class="text-center">فاکتور خرید</h2>
  <hr />

  <div class="row mb-4">
    <!-- ستون انتخاب‌ها -->
    <div class="col-md-2">
      <div class="mb-2">
        <widgets.searchinput placeholder="مشتری" textSearchUrl="/users/list" idSearchUrl="/users/" methode="POST"
          :querySearch="{ accountable: 1, }" :columns="[
            { label: 'نام', key: 'name' },
            { label: 'نام خانوادگی', key: 'lastname' },
            { label: 'موبایل', key: 'mobile' }
          ]" @searchableID="handleCustomer" />
      </div>

      <div>
        <widgets.searchinput placeholder="انبار" textSearchUrl="/products/warehouses/list"
          idSearchUrl="/products/warehouses/" methode="POST" :columns="[{ label: 'عنوان', key: 'title' }]"
          @searchableID="handleWarehouse" />
      </div>
    </div>

    <!-- اطلاعات انتخاب شده -->
    <div class="col">
      <div v-if="customer">
        <strong>مشتری:</strong>
        {{ customer.name }} {{ customer.lastname }} ({{ customer.mobile }})
      </div>
      <div v-if="warehouse" class="mt-1">
        <strong>انبار:</strong> {{ warehouse.title }}
      </div>
    </div>

    <!-- تاریخ و شماره فاکتور -->
    <div class="col-md-2">
      <dateFild :date="invoiceDate" />

      <div class="mt-2">
        <widgets.searchinput :def="invoice" placeholder="شماره فاکتور " textSearchUrl="0" idSearchUrl="/invoices/"
          methode="POST" @searchableID="handleInvoice" />
      </div>
    </div>
  </div>

  <!-- آیتم‌ها -->
  <div class="card mb-4">
    <div class="card-header">آیتم‌های فاکتور</div>
    <div class="card-body">
      <div class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
          <label class="form-label">محصول</label>
          <widgets.searchinput placeholder="محصول" textSearchUrl="/products/list" idSearchUrl="/products/"
            methode="POST" :columns="[
              { label: 'عنوان', key: 'title' },
              { label: 'فی', key: 'fi' },
              { label: 'موجودی', key: 'warehouse' }
            ]" @searchableID="handleProduct" />
        </div>

        <div class="col-md-2">
          <label class="form-label">تعداد</label>
          <input type="number" min="1" v-model.number="newItem.quantity" class="form-control" />
        </div>

        <div class="col-md-2">
          <label class="form-label">قیمت واحد</label>
          <input type="number" class="form-control" v-model.number="newItem.unitPrice" readonly />
        </div>

        <div class="col-md-2">
          <label class="form-label">تخفیف (%)</label>
          <input type="number" min="0" max="100" v-model.number="newItem.discountRate" class="form-control" />
        </div>

        <div class="col-md-1">
          <button class="btn btn-primary w-100" @click="addItem">
            افزودن
          </button>
        </div>
      </div>

      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>محصول</th>
            <th>تعداد</th>
            <th>فی</th>
            <th>تخفیف</th>
            <th>جمع</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="items.length === 0">
            <td colspan="7" class="text-center text-muted">
              آیتمی ثبت نشده
            </td>
          </tr>
          <tr v-for="(item, i) in items" :key="i">
            <td>{{ i + 1 }}</td>
            <td>{{ item.productName }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ format(item.unitPrice) }}</td>
            <td>{{ item.discountRate }}%</td>
            <td>{{ format(item.subtotal) }}</td>
            <td>
              <button class="btn btn-sm btn-danger" @click="removeItem(i)">
                حذف
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- جمع کل -->
  <div class="row justify-content-end">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span>مبلغ نهایی:</span>
            <strong>{{ format(totals.final) }}</strong>
          </div>

          <button class="btn btn-success w-100 mt-3" :disabled="loading" @click="submit">
            {{ loading ? 'در حال ثبت...' : 'ثبت فاکتور' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import dateFild from '@/components/widgets/dateFild'
import Swal from 'sweetalert2'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const baseUrl = config.public.apiBase

const loading = ref(false)

/* state اصلی */
const customer = ref(null)
const warehouse = ref(null)
const invoice = ref(null)
const product = ref(null)

const invoiceDate = ref(new Date().toLocaleDateString('fa-IR'))


invoice.value = await $api('invoices/lastid', { method: 'POST', })
/* آیتم‌ها */
const items = ref([])

const newItem = ref({
  quantity: 1,
  unitPrice: 0,
  discountRate: 0,
  subtotal: 0
})

/* handlers */
const handleCustomer = d => (customer.value = d)
const handleWarehouse = d => (warehouse.value = d)
const handleInvoice = d => (invoice.value = d)

const handleProduct = d => {
  product.value = d
  newItem.value.unitPrice = d.fi || 0
  calcSubtotal()
}

/* محاسبه */
const calcSubtotal = () => {
  const price = newItem.value.unitPrice * newItem.value.quantity
  const discount = price * (newItem.value.discountRate / 100)
  newItem.value.subtotal = price - discount
}

watch(
  () => [newItem.value.quantity, newItem.value.discountRate],
  calcSubtotal
)

/* افزودن آیتم */
const addItem = () => {
  if (!product.value) {
    Swal.fire('خطا', 'محصول انتخاب نشده', 'warning')
    return
  }

  items.value.push({
    productId: product.value.id,
    productName: product.value.title,
    quantity: newItem.value.quantity,
    unitPrice: newItem.value.unitPrice,
    discountRate: newItem.value.discountRate,
    subtotal: newItem.value.subtotal
  })

  newItem.value.quantity = 1
  newItem.value.discountRate = 0
  calcSubtotal()
}

/* حذف */
const removeItem = i => items.value.splice(i, 1)

/* جمع کل */
const totals = computed(() => ({
  final: items.value.reduce((s, i) => s + i.subtotal, 0)
}))

const format = v =>
  new Intl.NumberFormat('fa-IR').format(v)

/* ثبت */
const submit = async () => {
  if (!customer.value || !warehouse.value || items.value.length === 0) {
    Swal.fire('خطا', 'اطلاعات ناقص است', 'warning')
    return
  }

  loading.value = true

  try {
    await $api.post(`${baseUrl}/invoices`, {
      customer_id: customer.value.id,
      warehouse_id: warehouse.value.id,
      invoice_date: invoiceDate.value,
      items: items.value,
      total: totals.value.final
    })

    Swal.fire('موفق', 'فاکتور ثبت شد', 'success')
    items.value = []
  } finally {
    loading.value = false
  }
}
</script>
