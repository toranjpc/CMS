<template>
  <aside :class="['sidebar', { collapsed }]">
    <div class="sidebar-header d-flex align-items-center">
      <button class="toggle-btn d-none d-md-block me-2" @click="$emit('toggle')">
        <i class="fa fa-bars"></i>
      </button>
      <span class="sidebar-title" v-if="!collapsed">سیستم حسابداری</span>
    </div>

    <ul class="sidebar-menu">
      <!-- داشبورد -->
      <li>
        <NuxtLink to="/accounting" class="baseUrl" :class="{ active: isActive('/accounting') }">
          <i class="fa fa-home"></i>
          <span class="link-text">داشبورد</span>
        </NuxtLink>
      </li>

      <!-- محصولات -->
      <li :class="{ open: isProductsOpen }">
        <a href="javascript:void(0)" @click="toggleProducts">
          <i class="fa fa-box"></i>
          <span class="link-text">محصولات</span>
        </a>

        <ul class="submenu" v-show="isProductsOpen">
          <li>
            <NuxtLink to="/accounting/products" :class="{ active: isActive('/accounting/products') }">
              لیست محصولات
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/products/create" :class="{ active: isActive('/accounting/products/create') }">
              افزودن محصول
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- مشتریان -->
      <li :class="{ open: isCustomersOpen }">
        <a href="javascript:void(0)" @click="toggleCustomers">
          <i class="fa fa-users"></i>
          <span class="link-text">مشتریان</span>
        </a>

        <ul class="submenu" v-show="isCustomersOpen">
          <li>
            <NuxtLink to="/accounting/customers" :class="{ active: isActive('/accounting/customers') }">
              لیست مشتریان
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/customers/create" :class="{ active: isActive('/accounting/customers/create') }">
              افزودن مشتری
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- تأمین‌کنندگان -->
      <li :class="{ open: isSuppliersOpen }">
        <a href="javascript:void(0)" @click="toggleSuppliers">
          <i class="fa fa-truck"></i>
          <span class="link-text">تأمین‌کنندگان</span>
        </a>

        <ul class="submenu" v-show="isSuppliersOpen">
          <li>
            <NuxtLink to="/accounting/suppliers" :class="{ active: isActive('/accounting/suppliers') }">
              لیست تأمین‌کنندگان
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/suppliers/create" :class="{ active: isActive('/accounting/suppliers/create') }">
              افزودن تأمین‌کننده
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- فروش -->
      <li :class="{ open: isSalesOpen }">
        <a href="javascript:void(0)" @click="toggleSales">
          <i class="fa fa-shopping-cart"></i>
          <span class="link-text">فروش</span>
        </a>

        <ul class="submenu" v-show="isSalesOpen">
          <li>
            <NuxtLink to="/accounting/sales" :class="{ active: isActive('/accounting/sales') }">
              لیست فاکتورها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/sales/create" :class="{ active: isActive('/accounting/sales/create') }">
              ثبت فاکتور فروش
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- خرید -->
      <li :class="{ open: isPurchasesOpen }">
        <a href="javascript:void(0)" @click="togglePurchases">
          <i class="fa fa-shopping-bag"></i>
          <span class="link-text">خرید</span>
        </a>

        <ul class="submenu" v-show="isPurchasesOpen">
          <li>
            <NuxtLink to="/accounting/purchases" :class="{ active: isActive('/accounting/purchases') }">
              لیست خریدها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/purchases/create" :class="{ active: isActive('/accounting/purchases/create') }">
              ثبت خرید
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- دریافت‌ها -->
      <li :class="{ open: isReceiptsOpen }">
        <a href="javascript:void(0)" @click="toggleReceipts">
          <i class="fa fa-money-bill-wave"></i>
          <span class="link-text">دریافت‌ها</span>
        </a>

        <ul class="submenu" v-show="isReceiptsOpen">
          <li>
            <NuxtLink to="/accounting/receipts" :class="{ active: isActive('/accounting/receipts') }">
              لیست دریافت‌ها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/receipts/create" :class="{ active: isActive('/accounting/receipts/create') }">
              ثبت دریافت
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- پرداخت‌ها -->
      <li :class="{ open: isPaymentsOpen }">
        <a href="javascript:void(0)" @click="togglePayments">
          <i class="fa fa-credit-card"></i>
          <span class="link-text">پرداخت‌ها</span>
        </a>

        <ul class="submenu" v-show="isPaymentsOpen">
          <li>
            <NuxtLink to="/accounting/payments" :class="{ active: isActive('/accounting/payments') }">
              لیست پرداخت‌ها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/payments/create" :class="{ active: isActive('/accounting/payments/create') }">
              ثبت پرداخت
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- صندوق -->
      <li>
        <NuxtLink to="/accounting/cashbox" class="baseUrl" :class="{ active: isActive('/accounting/cashbox') }">
          <i class="fa fa-cash-register"></i>
          <span class="link-text">صندوق</span>
        </NuxtLink>
      </li>

      <!-- حساب‌های بانکی -->
      <li>
        <NuxtLink to="/accounting/banks" class="baseUrl" :class="{ active: isActive('/accounting/banks') }">
          <i class="fa fa-university"></i>
          <span class="link-text">حساب‌های بانکی</span>
        </NuxtLink>
      </li>

      <!-- هزینه‌ها -->
      <li :class="{ open: isExpensesOpen }">
        <a href="javascript:void(0)" @click="toggleExpenses">
          <i class="fa fa-dollar-sign"></i>
          <span class="link-text">هزینه‌ها</span>
        </a>

        <ul class="submenu" v-show="isExpensesOpen">
          <li>
            <NuxtLink to="/accounting/expenses" :class="{ active: isActive('/accounting/expenses') }">
              لیست هزینه‌ها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/accounting/expenses/create" :class="{ active: isActive('/accounting/expenses/create') }">
              ثبت هزینه
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- گزارشات -->
      <li>
        <NuxtLink to="/accounting/reports" class="baseUrl" :class="{ active: isActive('/accounting/reports') }">
          <i class="fa fa-chart-bar"></i>
          <span class="link-text">گزارشات</span>
        </NuxtLink>
      </li>

      <!-- تنظیمات -->
      <li>
        <NuxtLink to="/accounting/settings" class="baseUrl" :class="{ active: isActive('/accounting/settings') }">
          <i class="fa fa-cogs"></i>
          <span class="link-text">تنظیمات</span>
        </NuxtLink>
      </li>
    </ul>
  </aside>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()

/* وضعیت باز بودن منوها */
const isProductsOpen = ref(false)
const isCustomersOpen = ref(false)
const isSuppliersOpen = ref(false)
const isSalesOpen = ref(false)
const isPurchasesOpen = ref(false)
const isReceiptsOpen = ref(false)
const isPaymentsOpen = ref(false)
const isExpensesOpen = ref(false)

/* تشخیص active */
const isActive = (path) => {
  if (path === '/accounting') return route.path === '/accounting'
  return route.path.startsWith(path)
}

/* توابع باز/بسته کردن منوها */
const toggleProducts = () => { isProductsOpen.value = !isProductsOpen.value }
const toggleCustomers = () => { isCustomersOpen.value = !isCustomersOpen.value }
const toggleSuppliers = () => { isSuppliersOpen.value = !isSuppliersOpen.value }
const toggleSales = () => { isSalesOpen.value = !isSalesOpen.value }
const togglePurchases = () => { isPurchasesOpen.value = !isPurchasesOpen.value }
const toggleReceipts = () => { isReceiptsOpen.value = !isReceiptsOpen.value }
const togglePayments = () => { isPaymentsOpen.value = !isPaymentsOpen.value }
const toggleExpenses = () => { isExpensesOpen.value = !isExpensesOpen.value }

/* باز شدن خودکار بر اساس route */
watch(
  () => route.path,
  (path) => {
    isProductsOpen.value = path.startsWith('/accounting/products')
    isCustomersOpen.value = path.startsWith('/accounting/customers')
    isSuppliersOpen.value = path.startsWith('/accounting/suppliers')
    isSalesOpen.value = path.startsWith('/accounting/sales')
    isPurchasesOpen.value = path.startsWith('/accounting/purchases')
    isReceiptsOpen.value = path.startsWith('/accounting/receipts')
    isPaymentsOpen.value = path.startsWith('/accounting/payments')
    isExpensesOpen.value = path.startsWith('/accounting/expenses')
  },
  { immediate: true }
)

</script>

<style scoped>
.sidebar {
  background: #343a40;
  color: white;
  width: 250px;
  min-height: 100vh;
  transition: width 0.3s ease;
  position: fixed;
  right: 0;
  top: 0;
  z-index: 1000;
  overflow-y: auto;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid #495057;
  height: 70px;
}

.sidebar-title {
  font-size: 1.1rem;
  font-weight: bold;
  color: #fff;
}

.toggle-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu li {
  position: relative;
}

.sidebar-menu a {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #adb5bd;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: right;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.sidebar-menu i {
  margin-left: 0.5rem;
  width: 20px;
  text-align: center;
}

.link-text {
  margin-right: 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar.collapsed .link-text {
  display: none;
}

.sidebar.collapsed .sidebar-title {
  display: none;
}

/* Submenu styles */
.submenu {
  list-style: none;
  padding: 0;
  margin: 0;
  background: rgba(0, 0, 0, 0.2);
}

.submenu li a {
  padding: 0.5rem 1rem 0.5rem 2rem;
  font-size: 0.9rem;
}

.submenu li a:hover,
.submenu li a.active {
  background: rgba(255, 255, 255, 0.15);
}

/* Accordion animation */
li.open > a::after {
  content: '▼';
  margin-left: 0.5rem;
  transition: transform 0.3s ease;
}

li:not(.open) > a::after {
  content: '▶';
  margin-left: 0.5rem;
  transition: transform 0.3s ease;
}

.sidebar.collapsed li > a::after {
  display: none;
}
</style>
