<template>
  <Loading v-if="pageLoading" />
  <div v-else>
    <div v-if="isViewingFromList" class="alert alert-info py-2">
      حالت نمايش فعال است و امکان ويرايش وجود ندارد.
    </div>
    <hr />

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">نوع سند</label>
            <select class="form-select" v-model="transactionType" :disabled="isViewingFromList || Boolean(loadedTransaction?.id)">
              <option value="receive">دريافت</option>
              <option value="payment">پرداخت</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">شماره سند</label>
            <input type="text" class="form-control" v-model="transactionNumber" @input="loadExistingTransaction" :readonly="hasRouteTransaction" />
          </div>

          <div class="col-md-2">
            <label class="form-label">تاريخ سند</label>
            <dateFild v-model="transactionDate" />
          </div>

          <div class="col-md-3">
            <label class="form-label">طرف حساب</label>
            <widgets.searchinput
              placeholder="انتخاب طرف حساب"
              v-model="party"
              textSearchUrl="/users/list"
              idSearchUrl="/users/"
              methode="GET"
              :columns="[
                { label: 'نام', key: 'name' },
                { label: 'نام خانوادگي', key: 'lastname' },
                { label: 'موبايل', key: 'mobile' }
              ]"
              :disabled="isViewingFromList ? 1 : 0" />
          </div>

          <!-- Use shared TransactionWidget for common fields -->
          <div class="col-md-3">
            <label class="form-label">روش تسويه</label>
            <select class="form-select" v-model="transactionForm.payment_method" :disabled="isViewingFromList">
              <option value="cash">نقدي</option>
              <option value="card">کارت‌خوان</option>
              <option value="bank">بانکي</option>
              <option value="cheque">چک</option>
              <option value="account_to_account">حساب به حساب</option>
            </select>
          </div>

          <div class="col-md-3" v-if="transactionForm.payment_method === 'account_to_account'">
            <label class="form-label">ذی‌نفع (شخص ثالث)</label>
            <widgets.searchinput
              placeholder="انتخاب ذی‌نفع"
              v-model="transactionForm.beneficiary_party"
              textSearchUrl="/users/list"
              idSearchUrl="/users/"
              methode="GET"
              :columns="[
                { label: 'نام', key: 'name' },
                { label: 'نام خانوادگي', key: 'lastname' },
                { label: 'موبايل', key: 'mobile' }
              ]"
              :disabled="isViewingFromList ? 1 : 0" />
          </div>

          <div class="col-md-2">
            <label class="form-label">مبلغ</label>
            <widgets.CurrencyInput v-model="transactionForm.amount" :readonly="isViewingFromList" inputClass="form-control" />
          </div>

          <div class="col-md-4">
            <label class="form-label">شماره فاکتور مرتبط (اختياري)</label>
            <input
              type="text"
              class="form-control"
              v-model="invoiceNumber"
              placeholder="مثال: S2602110001"
              @blur="loadLinkedInvoice"
              :readonly="isViewingFromList" />
            <small v-if="invoiceNumber && !linkedInvoice" class="text-muted">
              شماره فاکتور را وارد کنيد و از فيلد خارج شويد تا بررسي شود.
            </small>
          </div>

          <div class="col-md-6" v-if="linkedInvoice">
            <label class="form-label">اطلاعات فاکتور متصل</label>
            <div class="border rounded p-2 bg-light">
              <div><strong>شماره:</strong> {{ linkedInvoice.invoice_number }}</div>
              <div>
                <strong>طرف حساب:</strong>
                {{ linkedInvoice.party?.name }} {{ linkedInvoice.party?.lastname }}
              </div>
              <div><strong>نوع فاکتور:</strong> {{ linkedInvoice.type === 'sell' ? 'فروش' : 'خريد' }}</div>
              <div><strong>مبلغ فاکتور:</strong> {{ format(linkedInvoice.total) }}</div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">شرح</label>
            <textarea class="form-control" rows="3" v-model="transactionForm.description" :readonly="isViewingFromList" placeholder="مثال: دريافت از مشتري و واريز به صندوق"></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
      <button v-if="!isViewingFromList" class="btn btn-success px-4" :disabled="loading" @click="submit">
        {{ loading ? 'در حال پردازش...' : submitButtonText }}
      </button>
      <button v-if="!isViewingFromList" class="btn btn-outline-secondary px-4" :disabled="loading" @click="startNewTransaction">
        سند جدید
      </button>
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
  middleware: 'auth',
  title: 'سند دريافت و پرداخت'
})

const { $api } = useNuxtApp()
const route = useRoute()

const pageLoading = ref(true)
const loading = ref(false)

const transactionType = ref('receive')
const transactionDate = ref(new Date().toISOString().split('T')[0])
const transactionNumber = ref('')

const party = ref(null)
const transactionForm = ref({
  amount: 0,
  payment_method: 'cash',
  beneficiary_party: null,
  transaction_date: new Date().toISOString().split('T')[0],
  description: ''
})

const invoiceNumber = ref('')
const linkedInvoice = ref(null)
const loadedTransaction = ref(null)

const hasRouteTransaction = computed(() => Boolean(route.query.transaction))
const isViewingFromList = computed(() => hasRouteTransaction.value && route.query.mode === 'view')
const submitButtonText = computed(() => (loadedTransaction.value?.id ? 'ويرايش سند' : 'ثبت سند'))

onMounted(async () => {
  try {
    await loadNextNumber()

    if (route.query.transaction) {
      transactionNumber.value = String(route.query.transaction)
      await loadExistingTransaction()
    }
  } finally {
    pageLoading.value = false
  }
})

watch(transactionType, async () => {
  if (!loadedTransaction.value?.id) {
    await loadNextNumber()
  }
})

const toNumber = value => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const normalizeBeneficiary = (tr) => tr?.beneficiaryParty || tr?.beneficiary_party || null

const format = v => new Intl.NumberFormat('fa-IR').format(toNumber(v))

const loadNextNumber = async () => {
  try {
    const response = await $api('transactions/lastid', {
      method: 'POST',
      body: { type: transactionType.value }
    })
    transactionNumber.value = response.data
  } catch (error) {
    console.error('Error getting transaction number:', error)
  }
}

const clearLinkedInvoice = () => {
  linkedInvoice.value = null
}

const loadLinkedInvoice = async () => {
  if (!invoiceNumber.value || invoiceNumber.value.trim() === '') {
    clearLinkedInvoice()
    return
  }

  try {
    const response = await $api('invoices/show-by-number', {
      method: 'POST',
      body: { invoice_number: invoiceNumber.value.trim() }
    })
    linkedInvoice.value = response.data
  } catch (error) {
    clearLinkedInvoice()
    Swal.fire({
      icon: 'warning',
      title: 'فاکتور يافت نشد',
      text: 'شماره فاکتور وارد شده معتبر نيست.'
    })
  }
}

const loadExistingTransaction = async () => {
  if (!transactionNumber.value || transactionNumber.value.trim() === '') return

  try {
    const response = await $api('transactions/show-by-number', {
      method: 'POST',
      body: { transaction_number: transactionNumber.value.trim() }
    })

    if (response.status === 'success') {
      const tr = response.data
      loadedTransaction.value = tr
      transactionType.value = tr.type
      transactionDate.value = tr.transaction_date
      party.value = tr.party
      transactionForm.value = {
        amount: toNumber(tr.amount),
        payment_method: tr.payment_method || 'cash',
        beneficiary_party: normalizeBeneficiary(tr),
        transaction_date: tr.transaction_date || transactionDate.value,
        description: tr.description || ''
      }
      transactionDate.value = transactionForm.value.transaction_date
      invoiceNumber.value = tr.invoice?.invoice_number || ''
      linkedInvoice.value = tr.invoice || null
    }
  } catch (error) {
    if (hasRouteTransaction.value) {
      Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: 'سند مورد نظر پيدا نشد.'
      })
    }
  }
}

const validateForm = async () => {
  if (!party.value || !party.value.id) {
    await Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'لطفا طرف حساب را انتخاب کنيد.'
    })
    return false
  }

  if (toNumber(transactionForm.value.amount) <= 0) {
    await Swal.fire({
      icon: 'warning',
      title: 'اطلاعات ناقص',
      text: 'مبلغ سند بايد بزرگ‌تر از صفر باشد.'
    })
    return false
  }

  if (transactionForm.value.payment_method === 'account_to_account') {
    if (!transactionForm.value.beneficiary_party || !transactionForm.value.beneficiary_party.id) {
      await Swal.fire({
        icon: 'warning',
        title: 'اطلاعات ناقص',
        text: 'در روش حساب به حساب، انتخاب ذی‌نفع الزامی است.'
      })
      return false
    }

    if (transactionForm.value.beneficiary_party.id === party.value.id) {
      await Swal.fire({
        icon: 'warning',
        title: 'اطلاعات نادرست',
        text: 'ذی‌نفع نباید با طرف حساب یکسان باشد.'
      })
      return false
    }
  }

  if (invoiceNumber.value && !linkedInvoice.value) {
    await loadLinkedInvoice()
    if (!linkedInvoice.value) return false
  }

  if (linkedInvoice.value) {
    const expectedType = linkedInvoice.value.type === 'sell' ? 'receive' : 'payment'
    const isPrimarySide =
      transactionType.value === expectedType &&
      linkedInvoice.value.party_id === party.value.id
    const isTransferCounterSide =
      paymentMethod.value === 'account_to_account' &&
      Boolean(loadedTransaction.value?.transfer_group_id) &&
      transactionType.value !== expectedType

    if (!isPrimarySide && !isTransferCounterSide) {
      await Swal.fire({
        icon: 'warning',
        title: 'عدم تطابق نوع',
        text: 'اطلاعات سند با فاکتور انتخابي همخواني ندارد.'
      })
      return false
    }

    if (!isTransferCounterSide && linkedInvoice.value.party_id !== party.value.id) {
      await Swal.fire({
        icon: 'warning',
        title: 'عدم تطابق طرف حساب',
        text: 'طرف حساب سند بايد با طرف حساب فاکتور يکسان باشد.'
      })
      return false
    }
  }

  return true
}

const resetForm = async () => {
  loadedTransaction.value = null
  party.value = null
  transactionForm.value = {
    amount: 0,
    payment_method: 'cash',
    beneficiary_party: null,
    transaction_date: new Date().toISOString().split('T')[0],
    description: ''
  }
  invoiceNumber.value = ''
  linkedInvoice.value = null
  transactionDate.value = new Date().toISOString().split('T')[0]
  await loadNextNumber()
}

const startNewTransaction = async () => {
  await resetForm()
}

const submit = async () => {
  const isValid = await validateForm()
  if (!isValid) return

  loading.value = true
  try {
    const payload = {
      type: transactionType.value,
      party_id: party.value.id,
      amount: toNumber(transactionForm.value.amount),
      payment_method: transactionForm.value.payment_method,
      beneficiary_party_id: transactionForm.value.beneficiary_party?.id || null,
      invoice_id: linkedInvoice.value?.id || null,
      transaction_date: transactionForm.value.transaction_date,
      description: transactionForm.value.description || null
    }

    const isEditing = Boolean(loadedTransaction.value?.id)
    let response

    if (isEditing) {
      response = await $api(`/transactions/${loadedTransaction.value.id}`, { method: 'PUT', body: payload })
      await loadExistingTransaction()
    } else {
      response = await $api('/transactions', { method: 'POST', body: payload })
      const created = response?.data || null
      if (created?.transaction_number) {
        transactionNumber.value = created.transaction_number
        await loadExistingTransaction()
      }
    }

    // برای account_to_account، پیام خاص نمایش می‌دهیم
    let successMessage = isEditing ? 'سند با موفقيت ويرايش شد' : 'سند با موفقيت ثبت شد'
    if (transactionForm.value.payment_method === 'account_to_account') {
      successMessage = isEditing ? 'دو سند حساب به حساب با موفقيت ويرايش شدند' : 'دو سند حساب به حساب با موفقيت ثبت شدند'
    }

    Swal.fire({
      icon: 'success',
      title: 'موفق',
      text: successMessage
    })
  } catch (error) {
    let errorMessage = 'مشکلي در ثبت سند رخ داده است.'
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
    loading.value = false
  }
}
</script>

<style scoped></style>

