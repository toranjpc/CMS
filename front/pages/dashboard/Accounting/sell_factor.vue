<template>

  <widgets.searchinput placeholder="واحد اندازه گیری" v-model="product.status" textSearchUrl="/products/units/list"
    idSearchUrl="/products/units/" methode="GET" :columns="[{ label: 'عنوان', key: 'title' }]"
    />
    <!-- :disabled="isView || !variant.convertUnit" -->
  <span v-if="product.status"> {{ product.status }}</span>

</template>

<script setup>
import Swal from 'sweetalert2'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'فاکتور فروش'
})
const { $api } = useNuxtApp()

const config = useRuntimeConfig()
const baseUrl = config.public.apiBase
const loading = ref(false)
const error = ref(null)

const product = ref({
  title: '',
  barcode: '',
  des: '',
  status: 1,
  form: null,
  tax_rate: 10,
  min_buy: 0,
  max_buy: 0,
  alert: 0
})

// const result = await Swal.fire({
//   title: 'تأیید حذف گروهی',
//   text: `آیا مطمئن هستید که می‌خواهید  برند انتخاب شده را حذف کنید؟`,
//   icon: 'warning',
//   showCancelButton: true,
//   confirmButtonColor: '#dc3545',
//   cancelButtonColor: '#6c757d',
//   confirmButtonText: 'بله، حذف کن',
//   cancelButtonText: 'لغو',
//   reverseButtons: true,
//   customClass: {
//     popup: 'swal-rtl'
//   }
// })
// if (!result.isConfirmed) alert()

</script>
