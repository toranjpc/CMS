// Windows-style UI composable for Nuxt
import { ref } from 'vue'

// Global windows state - singleton pattern
let windowsInstance: ReturnType<typeof createWindowsInstance> | null = null

function createWindowsInstance() {
  const openWindows = ref<any[]>([])
  const windowZIndex = ref(100)
  const isDragging = ref(false)
  const dragOffset = ref({ x: 0, y: 0 })
  const currentDraggedWindow = ref<HTMLElement | null>(null)
  const isDraggingIcon = ref(false)
  const currentDraggedIcon = ref<HTMLElement | null>(null)
  const iconDragOffset = ref({ x: 0, y: 0 })
  const startMenuActive = ref(false)
  const contextMenuActive = ref(false)
  const contextMenuPosition = ref({ x: 0, y: 0 })
  const selectedIcon = ref<string | null>(null)
  const dateTime = ref({ time: '--:--', date: '--/--/----' })

  // Menu items from dashboard sidebar
  const menuItems = {
    users_list: {
      title: 'لیست کاربران',
      icon: 'fa fa-users',
      route: '/dashboard/users',
      type: 'page',
      parent: 'users'
    },
    users_categories: {
      title: 'دسته‌بندی کاربران',
      icon: 'fa fa-folder',
      route: '/dashboard/users/categories',
      type: 'page',
      parent: 'users'
    },
    users_roles: {
      title: 'نقش‌های کاربری',
      icon: 'fa fa-user-shield',
      route: '/dashboard/users/roles',
      type: 'page',
      parent: 'users'
    },
    products_add: {
      title: 'ایجاد محصول جدید',
      icon: 'fa fa-plus',
      route: '/dashboard/products/add',
      type: 'page',
      parent: 'products'
    },
    products_list: {
      title: 'لیست محصولات',
      icon: 'fa fa-tags',
      route: '/dashboard/products',
      type: 'page',
      parent: 'products'
    },
    products_categories: {
      title: 'دسته‌بندی ها',
      icon: 'fa fa-folder-open',
      route: '/dashboard/products/categories',
      type: 'page',
      parent: 'products'
    },
    products_features: {
      title: 'ویژگی‌ها',
      icon: 'fa fa-star',
      route: '/dashboard/products/features',
      type: 'page',
      parent: 'products'
    },
    products_units: {
      title: 'واحد های اندازه گیری',
      icon: 'fa fa-ruler',
      route: '/dashboard/products/units',
      type: 'page',
      parent: 'products'
    },
    products_brands: {
      title: 'برندها',
      icon: 'fa fa-certificate',
      route: '/dashboard/products/brands',
      type: 'page',
      parent: 'products'
    },
    products_warehouses: {
      title: 'انبار ها',
      icon: 'fa fa-warehouse',
      route: '/dashboard/products/warehouses',
      type: 'page',
      parent: 'products'
    },
    accounting_buy_factor: {
      title: 'فاکتور خرید',
      icon: 'fa fa-file-invoice',
      route: '/dashboard/Accounting/buy_factor',
      type: 'page',
      parent: 'accounting'
    },
    accounting_buy_list: {
      title: 'لیست فاکتورهای خرید',
      icon: 'fa fa-list-alt',
      route: '/dashboard/Accounting/buy_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_sell_factor: {
      title: 'فاکتور فروش',
      icon: 'fa fa-file-invoice-dollar',
      route: '/dashboard/Accounting/sell_factor',
      type: 'page',
      parent: 'accounting'
    },
    accounting_sell_list: {
      title: 'لیست فاکتورهای فروش',
      icon: 'fa fa-list-alt',
      route: '/dashboard/Accounting/sell_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_pay_receipt: {
      title: 'ثبت سند دریافت/پرداخت',
      icon: 'fa fa-file-signature',
      route: '/dashboard/Accounting/pay_receipt',
      type: 'page',
      parent: 'accounting'
    },
    accounting_pay_receipt_list: {
      title: 'لیست اسناد دریافت/پرداخت',
      icon: 'fa fa-list-alt',
      route: '/dashboard/Accounting/pay_receipt_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_banks: {
      title: 'مدیریت بانک‌ها',
      icon: 'fa fa-university',
      route: '/dashboard/Accounting/banks',
      type: 'page',
      parent: 'accounting'
    },
    apps_list: {
      title: 'مدیریت پورتال‌ها',
      icon: 'fa fa-globe',
      route: '/dashboard/apps',
      type: 'page',
      parent: 'system'
    },
    plans_list: {
      title: 'مدیریت پلن‌ها',
      icon: 'fa fa-list-alt',
      route: '/dashboard/plans',
      type: 'page',
      parent: 'system'
    }
  }

  // Application data (for backward compatibility)
  const apps = {
    users_list: {
      title: 'لیست کاربران',
      icon: 'fa fa-users',
      route: '/dashboard/users',
      type: 'page',
      parent: 'users'
    },
    users_categories: {
      title: 'دسته‌بندی کاربران',
      icon: 'fa fa-folder',
      route: '/dashboard/users/categories',
      type: 'page',
      parent: 'users'
    },
    users_roles: {
      title: 'نقش‌های کاربری',
      icon: 'fa fa-user-shield',
      route: '/dashboard/users/roles',
      type: 'page',
      parent: 'users'
    },
     products_add: {
      title: 'ایجاد محصول جدید',
      icon: 'fa fa-plus',
      route: '/dashboard/products/add',
      type: 'page',
      parent: 'products'
    },
    products_list: {
      title: 'لیست محصولات',
      icon: 'fa fa-tags',
      route: '/dashboard/products',
      type: 'page',
      parent: 'products'
    },
    products_categories: {
      title: 'دسته‌بندی ها',
      icon: 'fa fa-folder-open',
      route: '/dashboard/products/categories',
      type: 'page',
      parent: 'products'
    },
    products_features: {
      title: 'ویژگی‌ها',
      icon: 'fa fa-star',
      route: '/dashboard/products/features',
      type: 'page',
      parent: 'products'
    },
    products_units: {
      title: 'واحد های اندازه گیری',
      icon: 'fa fa-ruler',
      route: '/dashboard/products/units',
      type: 'page',
      parent: 'products'
    },
    products_brands: {
      title: 'برندها',
      icon: 'fa fa-certificate',
      route: '/dashboard/products/brands',
      type: 'page',
      parent: 'products'
    },
    products_warehouses: {
      title: 'انبار ها',
      icon: 'fa fa-warehouse',
      route: '/dashboard/products/warehouses',
      type: 'page',
      parent: 'products'
    },
    accounting_buy_factor: {
      title: 'فاکتور خرید',
      icon: 'fa fa-file-invoice',
      route: '/dashboard/Accounting/buy_factor',
      type: 'page',
      parent: 'accounting'
    },
    accounting_buy_list: {
      title: 'لیست فاکتورهای خرید',
      icon: 'fa fa-list-alt',
      route: '/dashboard/Accounting/buy_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_sell_factor: {
      title: 'فاکتور فروش',
      icon: 'fa fa-file-invoice-dollar',
      route: '/dashboard/Accounting/sell_factor',
      type: 'page',
      parent: 'accounting'
    },
    accounting_sell_list: {
      title: 'لیست فاکتورهای فروش',
      icon: 'fa fa-list-check',
      route: '/dashboard/Accounting/sell_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_pay_receipt: {
      title: 'ثبت سند دریافت/پرداخت',
      icon: 'fa fa-file-signature',
      route: '/dashboard/Accounting/pay_receipt',
      type: 'page',
      parent: 'accounting'
    },
    accounting_pay_receipt_list: {
      title: 'لیست اسناد دریافت/پرداخت',
      icon: 'fa fa-file-lines',
      route: '/dashboard/Accounting/pay_receipt_list',
      type: 'page',
      parent: 'accounting'
    },
    accounting_banks: {
      title: 'مدیریت بانک‌ها',
      icon: 'fa fa-university',
      route: '/dashboard/Accounting/banks',
      type: 'page',
      parent: 'accounting'
    },
    apps_list: {
      title: 'مدیریت پورتال‌ها',
      icon: 'fa fa-globe',
      route: '/dashboard/apps',
      type: 'page',
      parent: 'system'
    },
    plans_list: {
      title: 'مدیریت پلن‌ها',
      icon: 'fa fa-list-alt',
      route: '/dashboard/plans',
      type: 'page',
      parent: 'system'
    }
  }
  // Update date and time
  const updateDateTime = () => {
    if (typeof window !== 'undefined') {
      const now = new Date()
      dateTime.value.time = now.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' })
      dateTime.value.date = now.toLocaleDateString('fa-IR')
    }
  }

  // Initialize date time
  const initializeDateTime = () => {
    updateDateTime()
    if (typeof window !== 'undefined') {
      setInterval(updateDateTime, 1000)
    }
  }

  // Toggle start menu
  const toggleStartMenu = () => {
    startMenuActive.value = !startMenuActive.value
  }

  const closeStartMenu = () => {
    startMenuActive.value = false
  }

  // Open application or page
  const openApp = (appName: string) => {
    const existingWindow = openWindows.value.find(w => w.appName === appName)
    if (existingWindow) {
      focusWindow(existingWindow.id)
      return
    }

    // Check menu items first
    const menuItem = menuItems[appName as keyof typeof menuItems]
    if (menuItem) {
      openPage(menuItem.route, menuItem.title, menuItem.icon, appName)
      return
    }

    // Fallback to apps
    const app = apps[appName as keyof typeof apps]
    if (!app) return

    // If app has route, use openPage
    if ((app as any).route) {
      openPage((app as any).route, app.title, app.icon, appName)
      return
    }

    // Otherwise use content (legacy apps)
    const windowId = `window-${Date.now()}`

    // Calculate center position
    let x = 100
    let y = 100
    if (typeof window !== 'undefined') {
      const windowWidth = 800
      const windowHeight = 600
      const desktopWidth = window.innerWidth
      const desktopHeight = window.innerHeight - 50 // minus taskbar
      x = Math.max(0, (desktopWidth - windowWidth) / 2)
      y = Math.max(0, (desktopHeight - windowHeight) / 2)
    }

    const windowData = {
      id: windowId,
      appName: appName,
      title: app.title,
      icon: app.icon,
      content: (app as any).content || '',
      route: null,
      x: x,
      y: y,
      minimized: false,
      maximized: false,
      zIndex: windowZIndex.value
    }

    openWindows.value.push(windowData)
    closeStartMenu()
    focusWindow(windowId)
  }

  // Open page in window
  const openPage = (route: string, title: string, icon: string, appName: string) => {
    const existingWindow = openWindows.value.find(w => w.route === route)
    if (existingWindow) {
      focusWindow(existingWindow.id)
      return
    }

    const windowId = `window-${Date.now()}`

    // Calculate center position
    let x = 100
    let y = 100
    if (typeof window !== 'undefined') {
      const windowWidth = 1000
      const windowHeight = 700
      const desktopWidth = window.innerWidth
      const desktopHeight = window.innerHeight - 50 // minus taskbar
      x = Math.max(0, (desktopWidth - windowWidth) / 2)
      y = Math.max(0, (desktopHeight - windowHeight) / 2)
    }

    const windowData = {
      id: windowId,
      appName: appName,
      title: title,
      icon: icon,
      route: route,
      content: null,
      x: x,
      y: y,
      width: 1000,
      height: 700,
      minimized: false,
      maximized: true, // Default to maximized
      zIndex: windowZIndex.value
    }

    openWindows.value.push(windowData)
    closeStartMenu()
    focusWindow(windowId)
  }

  // Focus window
  const focusWindow = (windowId: string) => {
    windowZIndex.value++
    const window = openWindows.value.find(w => w.id === windowId)
    if (window) {
      // If minimized, restore it to previous state
      if (window.minimized) {
        window.minimized = false
        // Restore to maximized state if it was maximized before
        if (window.wasMaximized) {
          window.maximized = true
        }
      }
      window.zIndex = windowZIndex.value
    }
  }

  // Close window
  const closeWindow = (windowId: string) => {
    const index = openWindows.value.findIndex(w => w.id === windowId)
    if (index !== -1) {
      openWindows.value.splice(index, 1)
    }
  }

  // Minimize window
  const minimizeWindow = (windowId: string) => {
    const window = openWindows.value.find(w => w.id === windowId)
    if (window) {
      // Save the maximized state before minimizing
      window.wasMaximized = window.maximized
      window.minimized = true
      // Don't change maximized state - keep it as is
    }
  }

  // Maximize window
  const maximizeWindow = (windowId: string) => {
    const window = openWindows.value.find(w => w.id === windowId)
    if (window) {
      if (window.maximized) {
        // Restore: use saved dimensions or default
        window.maximized = false
        if (window.restoredWidth) {
          window.width = window.restoredWidth
        } else {
          window.width = 1000
        }
        if (window.restoredHeight) {
          window.height = window.restoredHeight
        } else {
          window.height = 700
        }
        if (window.restoredX !== undefined) {
          window.x = window.restoredX
        } else {
          window.x = 100
        }
        if (window.restoredY !== undefined) {
          window.y = window.restoredY
        } else {
          window.y = 100
        }
      } else {
        // Maximize: save current dimensions
        window.restoredWidth = window.width || 1000
        window.restoredHeight = window.height || 700
        window.restoredX = window.x || 100
        window.restoredY = window.y || 100
        window.maximized = true
      }
    }
  }

  // Show context menu
  const showContextMenu = (x: number, y: number, appName: string) => {
    contextMenuPosition.value = { x, y }
    selectedIcon.value = appName
    contextMenuActive.value = true
  }

  // Hide context menu
  const hideContextMenu = () => {
    contextMenuActive.value = false
    selectedIcon.value = null
  }

  // Handle context action
  const handleContextAction = (action: string) => {
    if (!selectedIcon.value) return

    switch (action) {
      case 'open':
        openApp(selectedIcon.value)
        break
    }
    hideContextMenu()
  }

  // Icon positions
  const getIconPositions = () => {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('desktopIconPositions')
      return saved ? JSON.parse(saved) : {}
    }
    return {}
  }

  const saveIconPosition = (appName: string, x: number, y: number) => {
    if (typeof window !== 'undefined') {
      const positions = getIconPositions()
      positions[appName] = { x, y }
      localStorage.setItem('desktopIconPositions', JSON.stringify(positions))
    }
  }

  return {
    openWindows,
    windowZIndex,
    isDragging,
    dragOffset,
    currentDraggedWindow,
    isDraggingIcon,
    currentDraggedIcon,
    iconDragOffset,
    startMenuActive,
    contextMenuActive,
    contextMenuPosition,
    selectedIcon,
    dateTime,
    apps,
    menuItems,
    initializeDateTime,
    updateDateTime,
    toggleStartMenu,
    closeStartMenu,
    openApp,
    openPage,
    focusWindow,
    closeWindow,
    minimizeWindow,
    maximizeWindow,
    showContextMenu,
    hideContextMenu,
    handleContextAction,
    getIconPositions,
    saveIconPosition
  }
}

export const useWindows = () => {
  if (!windowsInstance) {
    windowsInstance = createWindowsInstance()
  }
  return windowsInstance
}
