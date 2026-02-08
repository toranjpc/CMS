<template>




  <widgets.searchinput def="" placeholder="انبار" textSearchUrl="/products/warehouses/list"
    idSearchUrl="/products/warehouses/" methode="POST" :columns="[{ label: 'عنوان', key: 'title' },]"
    :searchResultLable="r => r.title ? r.title : ''" @searchableID="handleWarehouse" />

  {{ Warehouse }}
</template>

<script setup>
import Swal from "sweetalert2";

definePageMeta({
  layout: "dashboard",
  middleware: "auth",
  title: "سند پرداخت",
});
const { $api } = useNuxtApp();

const config = useRuntimeConfig();
const baseUrl = config.public.apiBase;
const loading = ref(false);
const error = ref(null);

const userId = ref('')
const handleUserId = (data) => {
  userId.value = data
}
const Warehouse = ref('')
const handleWarehouse = (data) => {
  Warehouse.value = data
}


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
<style scoped></style>
