// Windows-style UI composable for Nuxt
import { ref } from 'vue'

export const useWindows = () => {
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

  // Application data
  const apps = {
    dashboard: {
      title: 'داشبورد',
      icon: 'fas fa-chart-line',
      content: `
        <h2>داشبورد مدیریتی</h2>
        <div class="content-grid">
          <div class="stat-card">
            <h3>کاربران فعال</h3>
            <div class="stat-value">1,234</div>
            <div class="stat-change">↑ 12% نسبت به ماه قبل</div>
          </div>
          <div class="stat-card">
            <h3>بازدید امروز</h3>
            <div class="stat-value">5,678</div>
            <div class="stat-change">↑ 8% نسبت به دیروز</div>
          </div>
          <div class="stat-card">
            <h3>درآمد ماهانه</h3>
            <div class="stat-value">12.5M</div>
            <div class="stat-change">↑ 15% نسبت به ماه قبل</div>
          </div>
          <div class="stat-card">
            <h3>سفارشات</h3>
            <div class="stat-value">892</div>
            <div class="stat-change">↑ 5% نسبت به ماه قبل</div>
          </div>
        </div>
      `
    },
    users: {
      title: 'مدیریت کاربران',
      icon: 'fas fa-users',
      content: `
        <h2>مدیریت کاربران</h2>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>شناسه</th>
                <th>نام کاربری</th>
                <th>ایمیل</th>
                <th>نقش</th>
                <th>تاریخ عضویت</th>
                <th>وضعیت</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>admin</td>
                <td>admin@example.com</td>
                <td>مدیر</td>
                <td>1402/01/15</td>
                <td>فعال</td>
              </tr>
            </tbody>
          </table>
        </div>
      `
    },
    settings: {
      title: 'تنظیمات سیستم',
      icon: 'fas fa-cog',
      content: `
        <h2>تنظیمات سیستم</h2>
        <div class="content-grid">
          <div class="stat-card">
            <h3>تنظیمات عمومی</h3>
            <p>تنظیمات کلی سیستم و پیکربندی</p>
          </div>
        </div>
      `
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

  // Open application
  const openApp = (appName: string) => {
    const existingWindow = openWindows.value.find(w => w.appName === appName)
    if (existingWindow) {
      focusWindow(existingWindow.id)
      return
    }

    const app = apps[appName as keyof typeof apps]
    if (!app) return

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
      content: app.content,
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

  // Focus window
  const focusWindow = (windowId: string) => {
    windowZIndex.value++
    const window = openWindows.value.find(w => w.id === windowId)
    if (window) {
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
      window.minimized = !window.minimized
    }
  }

  // Maximize window
  const maximizeWindow = (windowId: string) => {
    const window = openWindows.value.find(w => w.id === windowId)
    if (window) {
      window.maximized = !window.maximized
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
    initializeDateTime,
    updateDateTime,
    toggleStartMenu,
    closeStartMenu,
    openApp,
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
