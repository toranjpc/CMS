<template>
  <h2 class="text-center">فاکتور خرید</h2>
  <hr />

  <div class="row mb-4">
    <div class="col-md-2">
      <div class="mb-2">

        <widgets.searchinput placeholder="مشتری" v-model="customer" textSearchUrl="/users/list" idSearchUrl="/users/"
          methode="GET" :columns="[{ label: 'نام', key: 'name' },
          { label: 'نام خانوادگی', key: 'lastname' },
          { label: 'موبایل', key: 'mobile' }]" :disabled="0" />

      </div>

      <div>

        <widgets.searchinput placeholder="انبار" v-model="warehouse" textSearchUrl="/products/warehouses/list"
          idSearchUrl="/products/warehouses/" methode="POST" :columns="[{ label: 'عنوان', key: 'title' }]"
          :disabled="0" />

      </div>
    </div>

    <div class="col">
      <div v-if="customer">
        <strong>مشتری:</strong>
        {{ customer.name }} {{ customer.lastname }} ({{ customer.mobile }})
      </div>

      <div v-if="warehouse" class="mt-1">
        <strong>انبار:</strong> {{ warehouse.title }}
      </div>
    </div>

    <div class="col-md-2">
      <dateFild v-model="invoiceDate" />

      <div class="mt-2">
        <label class="form-label">شماره فاکتور</label>
        <input type="text" class="form-control" :value="invoiceNumber" readonly />
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">آیتم‌های فاکتور</div>

    <div class="card-body">
      <div class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
          <label class="form-label">محصول</label>

          <widgets.searchinput placeholder="محصول" v-model="product" textSearchUrl="/products/list"
            idSearchUrl="/products/" methode="POST" :columns="[{ label: 'عنوان', key: 'title' },
            { label: 'قیمت', key: 'price' },
            { label: 'موجودی', key: 'stock' }]" :disabled="0" />

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
            <td>{{ item.title }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ format(item.unit_price) }}</td>
            <td>{{ item.discount_rate }}%</td>
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

const loading = ref(false)

const customer = ref(null)
const warehouse = ref(null)
const invoice = ref(null)
const product = ref(null)

const invoiceDate = ref(new Date().toLocaleDateString('fa-IR'))
const invoiceNumber = ref(null)

onMounted(async () => {
  try {
    invoiceNumber.value = await $api('invoices/lastid', { method: 'POST' })
  } catch (error) {
    console.error('Error getting invoice number:', error)
  }
})

const items = ref([])

const newItem = ref({
  quantity: 1,
  unitPrice: 0,
  discountRate: 0,
  subtotal: 0
})

const handleCustomer = d => (customer.value = d)
const handleWarehouse = d => (warehouse.value = d)


watch(product, (newVal, oldVal) => {
  if (!newVal) return
  // product.value = newVal
  newItem.value.unitPrice = newVal.price || 0
  calcSubtotal()
})

const calcSubtotal = () => {
  const price = newItem.value.unitPrice * newItem.value.quantity
  const discount = price * (newItem.value.discountRate / 100)
  newItem.value.subtotal = price - discount
}

watch(
  () => [newItem.value.quantity, newItem.value.discountRate],
  calcSubtotal
)

const addItem = () => {
  if (!product.value || newItem.value.quantity <= 0) return

  items.value.push({
    product_id: product.value.id,
    title: product.value.title,
    quantity: newItem.value.quantity,
    unit_price: newItem.value.unitPrice,
    discount_rate: newItem.value.discountRate,
    subtotal: newItem.value.subtotal
  })

  product.value = null
  newItem.value.quantity = 1
  newItem.value.discountRate = 0
  newItem.value.unitPrice = 0
  newItem.value.subtotal = 0
}

const removeItem = i => items.value.splice(i, 1)

const totals = computed(() => ({
  final: items.value.reduce((s, i) => s + i.subtotal, 0)
}))

const format = v =>
  new Intl.NumberFormat('fa-IR').format(v)

const submit = async () => {
  if (!customer.value || !warehouse.value || items.value.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا مشتری، انبار و حداقل یک آیتم را انتخاب کنید'
    })
    return
  }

  loading.value = true

  try {
    const payload = {
      type: 'buy',
      party_id: customer.value.id,
      invoice_date: invoiceDate.value,
      subtotal: totals.value.final,
      total: totals.value.final,
      status: 'pending',
      items: items.value.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        total_price: item.subtotal
      }))
    }

    await $api.post('/invoices', payload)

    // Reset form
    customer.value = null
    warehouse.value = null
    items.value = []

    // Get new invoice number
    invoiceNumber.value = await $api('invoices/lastid', { method: 'POST' })

    Swal.fire({
      icon: 'success',
      title: 'ثبت موفق',
      text: 'فاکتور خرید با موفقیت ثبت شد'
    })
  } catch (error) {
    console.error('Submit error:', error)
    Swal.fire({
      icon: 'error',
      title: 'خطا در ثبت',
      text: error.response?.data?.message || 'مشکلی در ثبت فاکتور رخ داده است'
    })
  } finally {
    loading.value = false
  }
}
</script>
