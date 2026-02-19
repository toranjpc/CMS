<template>
  <Loading v-if="pageLoading" />
  <div v-else>
    <div v-if="isViewingFromList" class="alert alert-info py-2">
      حالت نمایش فعال است و امکان ویرایش وجود ندارد.
    </div>
    <hr />

    <div class="row mb-4">
      <div class="col-md-2">
        <div class="mb-2">

          <widgets.searchinput placeholder="مشتری" v-model="customer" textSearchUrl="/users/list" idSearchUrl="/users/"
            methode="GET" :columns="[{ label: 'نام', key: 'name' },
            { label: 'نام خانوادگی', key: 'lastname' },
            { label: 'موبایل', key: 'mobile' }]" :disabled="isViewingFromList ? 1 : 0" />

        </div>

        <div>

          <widgets.searchinput placeholder="انبار" v-model="warehouse" textSearchUrl="/products/warehouses/list"
            idSearchUrl="/products/warehouses/" methode="POST" :columns="[{ label: 'عنوان', key: 'title' }]"
            :disabled="isViewingFromList ? 1 : 0" />

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
          <input type="text" class="form-control" v-model="invoiceNumber" @input="loadExistingInvoice"
            :readonly="hasRouteInvoice" />
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">آیتم‌های فاکتور</div>

      <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
          <div class="col-md-5">
            <label class="form-label">محصول</label>

            <widgets.searchinput placeholder="محصول" v-model="product" textSearchUrl="/products/search-for-invoice"
              idSearchUrl="/products/" methode="POST" :querySearch="{ invoice_type: 'sell' }" :columns="[{ label: 'عنوان', key: 'display_name' },
              { label: 'قیمت فروش', key: 'default_price' },
              { label: 'موجودی', key: 'current_stock' }]" :disabled="isViewingFromList ? 1 : 0" />

          </div>

          <div class="col-md-2">
            <label class="form-label">تعداد</label>
            <input type="number" min="1" v-model.number="newItem.quantity" class="form-control"
              :readonly="isViewingFromList" />
          </div>

          <div class="col-md-3">
            <label class="form-label">قیمت واحد</label>
            <widgets.CurrencyInput v-model="newItem.unitPrice" :readonly="isViewingFromList"
              inputClass="form-control" />
          </div>

          <div class="col-md-1">
            <button class="btn btn-primary w-100" @click="addItem" :disabled="isViewingFromList">
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

            <template v-for="(item, i) in items" :key="i">
              <tr>
                <td>{{ i + 1 }}</td>
                <td>{{ item.title }}</td>
                <td>{{ item.quantity }}</td>
                <td>{{ format(item.unit_price) }}</td>
                <td>{{ format(item.subtotal) }}</td>
                <td>
                  <button class="btn btn-sm btn-danger" @click="removeItem(i)" :disabled="isViewingFromList">
                    حذف
                  </button>
                </td>
              </tr>
              <tr v-if="itemValidationErrors[i]">
                <td colspan="6" class="text-danger small py-1">
                  {{ itemValidationErrors[i] }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
        <div v-if="generalValidationErrors.length" class="alert alert-danger py-2 mb-0">
          <div v-for="(msg, idx) in generalValidationErrors" :key="`g-err-${idx}`">
            - {{ msg }}
          </div>
        </div>
      </div>
    </div>

    <div class="row justify-content-end">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <span>جمع کل:</span>
              <strong>{{ format(totals.subtotal) }}</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
              <span>تخفیف کل:</span>
              <input type="number" class="form-control form-control-sm w-50" v-model.number="invoiceDiscount"
                @input="calculateTotals" :readonly="isViewingFromList" />
            </div>

            <div class="d-flex justify-content-between mt-2">
              <span>مالیات:</span>
              <strong>{{ format(totals.tax) }}</strong>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
              <span>مبلغ نهایی:</span>
              <strong>{{ format(totals.final) }}</strong>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button v-if="!isViewingFromList" class="btn btn-success w-100" :disabled="loading" @click="submit">
                {{ loading ? 'در حال پردازش...' : submitButtonText }}
              </button>
              <button v-if="!isViewingFromList" class="btn btn-outline-secondary" :disabled="loading"
                @click="startNewInvoice">
                فاکتور جدید
              </button>
            </div>
            <button v-if="loadedInvoice?.id && !isViewingFromList" class="btn btn-outline-primary w-100 mt-2"
              :disabled="transactionLookupLoading" @click="openTransactionWidgetForInvoice(loadedInvoice)">
              {{ transactionLookupLoading ? 'در حال دریافت اسناد...' : transactionTitle }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showTransactionWidget" class="transaction-widget-overlay">
      <div class="transaction-widget-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="m-0">{{ transactionTitle }} برای فاکتور</h5>
          <button class="btn btn-sm btn-outline-secondary" @click="closeTransactionWidget">بستن</button>
        </div>

        <div class="mb-3">
          <div><strong>شماره فاکتور:</strong> {{ savedInvoiceForTransaction?.invoice_number }}</div>
          <div><strong>مبلغ فاکتور:</strong> {{ format(savedInvoiceForTransaction?.total || 0) }}</div>
        </div>

        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>لیست اسناد این فاکتور</strong>
            <button class="btn btn-sm btn-outline-primary" :disabled="transactionLoading" @click="startNewTransaction">
              ایجاد سند جدید
            </button>
          </div>

          <div v-if="transactionListLoading" class="text-muted small">در حال بارگذاری اسناد...</div>
          <div v-else-if="transactionList.length === 0" class="text-muted small">برای این فاکتور هنوز سندی ثبت نشده است.
          </div>
          <div v-else class="transaction-list">
            <button v-for="tr in transactionList" :key="tr.id" type="button" class="transaction-list-item"
              :class="{ active: currentTransaction?.id === tr.id }" @click="selectTransactionForEdit(tr)">
              <span>{{ tr.transaction_number }}</span>
              <span>{{ format(tr.amount) }}</span>
            </button>
          </div>
        </div>

        <div v-if="showTransactionForm">
          <widgets.TransactionWidget v-model="transactionForm" />

          <div class="d-flex gap-2">
            <button class="btn btn-success flex-grow-1" :disabled="transactionLoading" @click="submitTransaction">
              {{ transactionLoading ? 'در حال ثبت...' : transactionSubmitText }}
            </button>
            <button class="btn btn-outline-secondary" :disabled="transactionLoading"
              @click="showTransactionForm = false">
              انصراف
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Loading from '@/components/Loading.vue'
import dateFild from '@/components/widgets/dateFild'
import CurrencyInput from '@/components/widgets/CurrencyInput.vue'
import Swal from 'sweetalert2'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const { $api } = useNuxtApp()
const route = useRoute()

const pageLoading = ref(true)
const loading = ref(false)

const customer = ref(null)
const warehouse = ref(null)
const invoice = ref(null)
const product = ref(null)

const invoiceDate = ref(new Date().toISOString().split('T')[0])
const invoiceNumber = ref(null)
const loadedInvoice = ref(null)
const invoiceDiscount = ref(0)
const savedInvoiceForTransaction = ref(null)
const showTransactionWidget = ref(false)
const showTransactionForm = ref(false)
const transactionLoading = ref(false)
const transactionLookupLoading = ref(false)
const transactionListLoading = ref(false)
const transactionList = ref([])
const currentTransaction = ref(null)
const transactionForm = ref({
  amount: 0,
  payment_method: 'cash',
  beneficiary_party: null,
  transaction_date: new Date().toISOString().split('T')[0],
  description: ''
})
const hasRouteInvoice = computed(() => Boolean(route.query.invoice))
const isViewingFromList = computed(() => hasRouteInvoice.value && route.query.mode === 'view')
const submitButtonText = computed(() =>
  loadedInvoice.value?.id ? 'ویرایش فاکتور' : 'ثبت فاکتور'
)
const transactionTypeForInvoice = computed(() =>
  savedInvoiceForTransaction.value?.type === 'buy' ? 'payment' : 'receive'
)
const transactionTitle = computed(() =>
  transactionTypeForInvoice.value === 'payment' ? 'سند پرداخت' : 'سند دریافت'
)
const transactionSubmitText = computed(() =>
  currentTransaction.value?.id ? `ویرایش ${transactionTitle.value}` : `ثبت ${transactionTitle.value}`
)

onMounted(async () => {
  try {
    const response = await $api('invoices/lastid', { method: 'POST', body: { type: 'sell' } })
    invoiceNumber.value = response.data

    if (route.query.invoice) {
      invoiceNumber.value = String(route.query.invoice)
      await loadExistingInvoice()
    }
  } catch (error) {
    console.error('Error getting invoice number:', error)
  } finally {
    pageLoading.value = false
  }
})

const items = ref([])
const itemValidationErrors = ref({})
const generalValidationErrors = ref([])

const newItem = ref({
  quantity: 1,
  unitPrice: 0,
  discountRate: 0,
  subtotal: 0
})

const handleCustomer = d => (customer.value = d)
const handleWarehouse = d => (warehouse.value = d)

// Product price management
const productPrices = ref({})

const getLastUsedPrice = (productId) => {
  return productPrices.value[productId] || null
}

const saveUsedPrice = (productId, price) => {
  productPrices.value[productId] = price
  // Save to localStorage
  if (process.client) {
    localStorage.setItem('sell_product_prices', JSON.stringify(productPrices.value))
  }
}

// Load saved prices on mount
// Load saved prices on mount
if (process.client) {
  const saved = localStorage.getItem('sell_product_prices')
  if (saved) {
    productPrices.value = JSON.parse(saved)
  }
}

watch(product, (newVal, oldVal) => {
  if (!newVal) return
  // Use last used price if available, otherwise use default price
  const lastPrice = getLastUsedPrice(newVal.id)
  newItem.value.unitPrice = lastPrice !== null ? lastPrice : (newVal.last_used_price || newVal.default_price || 0)
  calcSubtotal()
})

const calcSubtotal = () => {
  newItem.value.subtotal = newItem.value.unitPrice * newItem.value.quantity
}

watch(
  () => [newItem.value.quantity, newItem.value.unitPrice],
  calcSubtotal
)

const addItem = () => {
  if (!product.value || newItem.value.quantity <= 0) return
  if (!warehouse.value || !warehouse.value.id) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا ابتدا انبار را انتخاب کنید'
    })
    return
  }

  const productId = product.value.f_id || product.value.mainProduct?.id
  if (!productId) {
    Swal.fire({
      icon: 'error',
      title: 'خطا',
      text: 'محصول انتخاب شده معتبر نیست'
    })
    return
  }

  // Save the used price for this product
  saveUsedPrice(product.value.id, newItem.value.unitPrice)

  const existingIndex = items.value.findIndex((row) =>
    row.product_item_id === product.value.id &&
    row.warehouse_id === warehouse.value.id
  )

  if (existingIndex >= 0) {
    const existing = items.value[existingIndex]
    const oldQty = toNumber(existing.quantity)
    const oldSubtotal = toNumber(existing.subtotal)
    const addQty = toNumber(newItem.value.quantity)
    const addSubtotal = toNumber(newItem.value.subtotal)
    const mergedQty = oldQty + addQty
    const mergedSubtotal = oldSubtotal + addSubtotal
    const mergedUnitPrice = mergedQty > 0 ? (mergedSubtotal / mergedQty) : 0

    items.value[existingIndex] = {
      ...existing,
      quantity: mergedQty,
      unit_price: mergedUnitPrice,
      subtotal: mergedSubtotal
    }
  } else {
    items.value.push({
      product_item_id: product.value.id, // استفاده از product_item_id
      warehouse_id: warehouse.value.id,
      product_id: productId, // استفاده از product_id والد
      title: product.value.display_name,
      quantity: newItem.value.quantity,
      unit_price: newItem.value.unitPrice,
      subtotal: newItem.value.subtotal
    })
  }

  product.value = null
  newItem.value.quantity = 1
  newItem.value.unitPrice = 0
  newItem.value.subtotal = 0
  clearValidationErrors()
}

const removeItem = i => {
  items.value.splice(i, 1)
  clearValidationErrors()
}

const calculateTotals = () => {
  // این تابع برای trigger شدن هنگام تغییر تخفیف فراخوانی می‌شود
}

const toNumber = value => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const totals = computed(() => {
  const subtotal = items.value.reduce((s, i) => s + toNumber(i.subtotal), 0)
  const discount = toNumber(invoiceDiscount.value)
  const taxRate = 10 // نرخ مالیات پیش‌فرض
  const tax = (subtotal - discount) * (taxRate / 100)

  return {
    subtotal,
    discount,
    tax,
    final: subtotal - discount + tax
  }
})

const format = v =>
  new Intl.NumberFormat('fa-IR').format(v)

const clearValidationErrors = () => {
  itemValidationErrors.value = {}
  generalValidationErrors.value = []
}

const applyServerValidationErrors = (errors) => {
  clearValidationErrors()
  if (!errors) return

  for (const field in errors) {
    const messages = errors[field] || []
    const match = field.match(/^items\.(\d+)(?:\..+)?$/)
    if (match) {
      const index = Number(match[1])
      itemValidationErrors.value[index] = messages.join(' | ')
      continue
    }

    if (field === 'items') {
      generalValidationErrors.value.push(...messages)
      continue
    }

    generalValidationErrors.value.push(...messages)
  }
}

const extractBackendErrorMessage = (error, fallback = 'مشکلی در ثبت فاکتور رخ داده است') => {
  const backendErrors = error?.response?.data?.errors
  if (backendErrors) {
    const firstKey = Object.keys(backendErrors)[0]
    const firstMessage = backendErrors[firstKey]?.[0]
    if (firstMessage) return firstMessage
  }

  return error?.response?.data?.message || fallback
}

const closeTransactionWidget = () => {
  showTransactionWidget.value = false
  showTransactionForm.value = false
  savedInvoiceForTransaction.value = null
  currentTransaction.value = null
  transactionList.value = []
}

const normalizeBeneficiary = (tr) => tr?.beneficiaryParty || tr?.beneficiary_party || null

const applyTransactionToForm = (invoiceData, transactionData = null) => {
  if (!invoiceData?.id) return

  savedInvoiceForTransaction.value = invoiceData
  currentTransaction.value = transactionData
  transactionForm.value = {
    amount: toNumber(transactionData?.amount ?? invoiceData.total),
    payment_method: transactionData?.payment_method || 'cash',
    beneficiary_party: normalizeBeneficiary(transactionData),
    transaction_date: transactionData?.transaction_date || new Date().toISOString().split('T')[0],
    description: transactionData?.description || `ثبت خودکار ${invoiceData.type === 'sell' ? 'سند دریافت' : 'سند پرداخت'} برای فاکتور ${invoiceData.invoice_number}`
  }
  showTransactionForm.value = true
}

const startNewTransaction = () => {
  if (!savedInvoiceForTransaction.value?.id) return
  applyTransactionToForm(savedInvoiceForTransaction.value, null)
}

const selectTransactionForEdit = (transactionData) => {
  if (!savedInvoiceForTransaction.value?.id || !transactionData?.id) return
  applyTransactionToForm(savedInvoiceForTransaction.value, transactionData)
}

const fetchInvoiceTransactions = async (invoiceData, preferTransactionId = null) => {
  if (!invoiceData?.id) return

  transactionListLoading.value = true
  try {
    const response = await $api('/transactions', {
      method: 'GET',
      query: {
        invoice_id: invoiceData.id,
        type: invoiceData.type === 'buy' ? 'payment' : 'receive',
        limit: 100
      }
    })

    transactionList.value = response?.data?.items || []
    if (preferTransactionId) {
      const selected = transactionList.value.find((tr) => tr.id === preferTransactionId)
      if (selected) {
        selectTransactionForEdit(selected)
      }
    }
  } catch (error) {
    transactionList.value = []
  } finally {
    transactionListLoading.value = false
  }
}

const openTransactionWidgetForInvoice = async (invoiceData) => {
  if (!invoiceData?.id) return

  savedInvoiceForTransaction.value = invoiceData
  showTransactionWidget.value = true
  showTransactionForm.value = false
  currentTransaction.value = null
  transactionLookupLoading.value = true
  try {
    await fetchInvoiceTransactions(invoiceData)
  } catch (error) {
    // ignore
  } finally {
    transactionLookupLoading.value = false
  }
}

const resetInvoiceForm = async () => {
  customer.value = null
  warehouse.value = null
  items.value = []
  loadedInvoice.value = null
  invoiceDiscount.value = 0
  invoiceNumber.value = null
  product.value = null
  savedInvoiceForTransaction.value = null
  showTransactionWidget.value = false
  currentTransaction.value = null
  transactionList.value = []
  await fetchNewInvoiceNumber()
}

const fetchNewInvoiceNumber = async () => {
  const response = await $api('invoices/lastid', { method: 'POST', body: { type: 'sell' } })
  invoiceNumber.value = response.data
}

const startNewInvoice = async () => {
  await resetInvoiceForm()
}

const loadExistingInvoice = async () => {
  if (!invoiceNumber.value || invoiceNumber.value.trim() === '') return

  try {
    const response = await $api('invoices/show-by-number', {
      method: 'POST',
      body: { invoice_number: invoiceNumber.value.trim() }
    })

    if (response.status === 'success') {
      loadedInvoice.value = response.data

      // Load invoice data
      customer.value = response.data.party
      invoiceDate.value = response.data.date
      invoiceDiscount.value = toNumber(response.data.discount)
      const firstItemWarehouse = response.data.items?.[0]?.warehouse
      if (firstItemWarehouse) {
        warehouse.value = {
          id: firstItemWarehouse.id,
          title: firstItemWarehouse.title
        }
      }

      // Load items
      items.value = response.data.items.map(item => ({
        product_item_id: item.product_item_id,
        warehouse_id: item.warehouse_id,
        product_id: item.product_id,
        title: item.productItem ? (item.productItem.mainProduct?.title + ' - ' + item.productItem.title) : item.product.title,
        quantity: toNumber(item.quantity),
        unit_price: toNumber(item.unit_price),
        subtotal: toNumber(item.total_price)
      }))

      // Update saved prices with loaded invoice prices
      response.data.items.forEach(item => {
        const itemId = item.product_item_id || item.product_id
        saveUsedPrice(itemId, item.unit_price)
      })

      // Swal.fire({
      //   icon: 'info',
      //   title: 'فاکتور یافت شد',
      //   text: 'اطلاعات فاکتور بارگذاری شد'
      // })
    }
  } catch (error) {
    console.error('Error loading invoice:', error)
    // Don't show error for invoice not found, just continue
  }
}

const submit = async () => {
  clearValidationErrors()
  if (!customer.value || !customer.value.id) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا مشتری را انتخاب کنید'
    })
    return
  }

  if (items.value.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا حداقل یک آیتم را اضافه کنید'
    })
    return
  }
  if (!warehouse.value || !warehouse.value.id) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا انبار را انتخاب کنید'
    })
    return
  }

  // Validate items
  for (let i = 0; i < items.value.length; i++) {
    const item = items.value[i]
    if (!item.product_id) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: `آیتم ${i + 1}: محصول انتخاب شده معتبر نیست`
      })
      return
    }
    if (!item.product_item_id) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: `آیتم ${i + 1}: تنوع محصول انتخاب شده معتبر نیست`
      })
      return
    }
    if (!item.warehouse_id) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: `آیتم ${i + 1}: انبار انتخاب نشده است`
      })
      return
    }
    if (!item.quantity || item.quantity <= 0) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: `آیتم ${i + 1}: تعداد باید بزرگتر از صفر باشد`
      })
      return
    }
    if (!item.unit_price || item.unit_price <= 0) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: `آیتم ${i + 1}: قیمت واحد باید بزرگتر از صفر باشد`
      })
      return
    }
  }

  loading.value = true

  try {
    const payload = {
      type: 'sell',
      party_id: customer.value.id,
      date: invoiceDate.value,
      subtotal: totals.value.subtotal,
      discount: invoiceDiscount.value,
      tax: totals.value.tax,
      total: totals.value.final,
      status: 'draft',
      items: items.value.map(item => ({
        product_item_id: item.product_item_id,
        warehouse_id: item.warehouse_id || warehouse.value.id,
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        total_price: item.subtotal
      }))
    }

    console.log('Submitting payload:', payload)

    let savedInvoice = null
    if (loadedInvoice.value?.id) {
      const response = await $api(`/invoices/${loadedInvoice.value.id}`, { method: 'PUT', body: payload })
      if (response?.status && response.status !== 'success') {
        const err = new Error(response.message || 'validation_error')
        err.response = { status: 422, data: response }
        throw err
      }
      savedInvoice = response?.data || loadedInvoice.value
    } else {
      const response = await $api('/invoices', { method: 'POST', body: payload })
      if (response?.status && response.status !== 'success') {
        const err = new Error(response.message || 'validation_error')
        err.response = { status: 422, data: response }
        throw err
      }
      savedInvoice = response?.data || null
    }

    // بعد از ذخیره روی همان فاکتور بمانیم و حالت ویرایش را فعال کنیم
    if (savedInvoice?.id) {
      loadedInvoice.value = savedInvoice
      invoiceNumber.value = savedInvoice.invoice_number
      await loadExistingInvoice()
    }

    await openTransactionWidgetForInvoice(savedInvoice || loadedInvoice.value)

    // Swal.fire({
    //   icon: 'success',
    //   title: 'ثبت موفق',
    //   text: loadedInvoice.value?.id ? 'فاکتور فروش با موفقیت ویرایش شد' : 'فاکتور فروش با موفقیت ثبت شد'
    // })
  } catch (error) {
    console.error('Submit error:', error)
    console.error('Error response:', error.response?.data)

    let errorMessage = extractBackendErrorMessage(error)

    if (error.response?.status === 422 && error.response?.data?.errors) {
      // Validation errors
      const errors = error.response.data.errors
      applyServerValidationErrors(errors)
    }

    Swal.fire({
      icon: 'error',
      title: 'خطا در ثبت',
      text: errorMessage
    })
  } finally {
    loading.value = false
  }
}

const submitTransaction = async () => {
  if (!savedInvoiceForTransaction.value?.id) return

  if (!transactionForm.value.amount || toNumber(transactionForm.value.amount) <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'مبلغ سند باید بزرگتر از صفر باشد'
    })
    return
  }

  if (transactionForm.value.payment_method === 'account_to_account') {
    if (!transactionForm.value.beneficiary_party?.id) {
      Swal.fire({
        icon: 'warning',
        title: 'اطلاعات ناقص',
        text: 'در روش حساب به حساب، انتخاب ذی‌نفع الزامی است'
      })
      return
    }

    if (transactionForm.value.beneficiary_party.id === savedInvoiceForTransaction.value.party_id) {
      Swal.fire({
        icon: 'warning',
        title: 'اطلاعات نادرست',
        text: 'ذی‌نفع نباید با طرف حساب فاکتور یکسان باشد'
      })
      return
    }
  }

  transactionLoading.value = true
  try {
    const isEditing = Boolean(currentTransaction.value?.id)
    const payload = {
      type: transactionTypeForInvoice.value,
      party_id: savedInvoiceForTransaction.value.party_id,
      amount: toNumber(transactionForm.value.amount),
      payment_method: transactionForm.value.payment_method,
      beneficiary_party_id: transactionForm.value.beneficiary_party?.id || null,
      invoice_id: savedInvoiceForTransaction.value.id,
      transaction_date: transactionForm.value.transaction_date,
      description: transactionForm.value.description || null
    }

    if (currentTransaction.value?.id) {
      const response = await $api(`/transactions/${currentTransaction.value.id}`, { method: 'PUT', body: payload })
      await fetchInvoiceTransactions(savedInvoiceForTransaction.value, response?.data?.id || currentTransaction.value.id)
    } else {
      const response = await $api('/transactions', { method: 'POST', body: payload })
      await fetchInvoiceTransactions(savedInvoiceForTransaction.value, response?.data?.id)
    }
    Swal.fire({
      icon: 'success',
      title: 'موفق',
      text: isEditing
        ? `${transactionTitle.value} با موفقیت ویرایش شد`
        : `${transactionTitle.value} با موفقیت ثبت شد`
    })
  } catch (error) {
    let errorMessage = `خطا در ثبت ${transactionTitle.value}`
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const errors = error.response.data.errors
      const messages = []
      for (const field in errors) {
        messages.push(...errors[field])
      }
      errorMessage = messages.join('\n')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    Swal.fire({
      icon: 'error',
      title: 'خطا',
      text: errorMessage
    })
  } finally {
    transactionLoading.value = false
  }
}
</script>

<style scoped>
.transaction-widget-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.transaction-widget-card {
  width: 100%;
  max-width: 520px;
  background: #fff;
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.transaction-list {
  max-height: 180px;
  overflow: auto;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.transaction-list-item {
  width: 100%;
  display: flex;
  justify-content: space-between;
  border: none;
  border-bottom: 1px solid #eee;
  background: #fff;
  padding: 8px 10px;
  cursor: pointer;
}

.transaction-list-item:last-child {
  border-bottom: none;
}

.transaction-list-item.active {
  background: #eef5ff;
  font-weight: 600;
}
</style>