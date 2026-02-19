<template>
  <div dir="rtl" lang="fa" class="windows-layout">
    <!-- Desktop Background -->
    <div class="desktop">
      <!-- Desktop Icons -->
      <div class="desktop-icons" ref="desktopIconsRef">
        <div 
          v-for="(item, itemKey, index) in menuItems" 
          :key="itemKey"
          class="desktop-icon"
          :class="{ 
            selected: selectedIcon === itemKey, 
            dragging: isDraggingIcon && iconDragging.appName === itemKey 
          }"
          :data-app="itemKey"
          :style="getIconStyle(itemKey, index)"
          @dblclick="openApp(itemKey)"
          @mousedown="startIconDrag($event, itemKey, index)"
          @click="selectIcon(itemKey)"
          @contextmenu="handleContextMenu($event, itemKey)"
        >
          <div class="icon-image">
            <i :class="item.icon"></i>
          </div>
          <span class="icon-label">{{ item.title }}</span>
        </div>
      </div>

      <!-- Application Windows -->
      <div class="windows-container">
        <div
          v-for="window in openWindows"
          :key="window.id"
          class="window"
          :class="{ minimized: window.minimized, maximized: window.maximized }"
          :style="getWindowStyle(window)"
          @mousedown="focusWindow(window.id)"
        >
          <div class="window-header" @mousedown="startDrag($event, window.id)">
            <div class="window-title">
              <i :class="window.icon"></i>
              <span>{{ window.title }}</span>
            </div>
            <div class="window-controls">
              <button class="window-control minimize" @click="minimizeWindow(window.id)" title="کوچک کردن">
                <i class="fas fa-minus"></i>
              </button>
              <button class="window-control maximize" @click="maximizeWindow(window.id)" :title="window.maximized ? 'بازگرداندن' : 'بزرگ کردن'">
                <i :class="window.maximized ? 'fas fa-window-restore' : 'fas fa-square'"></i>
              </button>
              <button class="window-control close" @click="closeWindow(window.id)" title="بستن">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="window-content" v-show="!window.minimized">
            <WindowPageLoader v-if="window.route" :route="window.route" :key="window.id" />
            <div v-else-if="window.content" v-html="window.content"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Taskbar -->
    <div class="taskbar">
      <div class="taskbar-left">
        <button 
          class="start-button" 
          :class="{ active: startMenuActive }"
          @click="toggleStartMenu"
        >
          <i class="fab fa-windows"></i>
        </button>
        <div class="taskbar-apps">
          <div
            v-for="window in openWindows"
            :key="window.id"
            class="taskbar-app"
            :class="{ 
              active: window.zIndex === windowZIndex && !window.minimized,
              minimized: window.minimized
            }"
            @click="focusWindow(window.id)"
          >
            <i :class="window.icon"></i>
            <span>{{ window.title }}</span>
          </div>
        </div>
      </div>
      
      <div class="taskbar-right">
        <div class="system-tray">
          <!-- Notification Icon -->
          <div 
            class="tray-icon notification-icon" 
            :class="{ active: notificationDropdownActive, hasUnread: unreadCount > 0 }"
            @click.stop="toggleNotificationDropdown"
            ref="notificationIconRef"
          >
            <i class="fas fa-bell"></i>
            <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
          </div>
          
          <div class="tray-icon">
            <i class="fas fa-wifi"></i>
          </div>
          <div 
            class="tray-icon volume-icon" 
            :class="{ muted: isMuted }"
            @click.stop="toggleMute"
            title="قطع/وصل صدا"
          >
            <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
          </div>
          <div 
            class="tray-icon battery-icon" 
            :class="{ active: batteryDropdownActive }"
            @click.stop="toggleBatteryDropdown"
            ref="batteryIconRef"
          >
            <i class="fas fa-battery-three-quarters"></i>
          </div>
          <div class="datetime">
            <span class="time">{{ dateTime.time }}</span>
            <span class="date">{{ dateTime.date }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Start Menu -->
    <div class="start-menu" :class="{ active: startMenuActive }" @click.stop>
      <div class="start-menu-header">
        <div class="user-info">
          <div class="user-avatar">
            <i class="fas fa-user"></i>
          </div>
          <div class="user-details">
            <div class="user-name">مدیر سیستم</div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
      </div>
      
      <div class="start-menu-content">
        <div class="start-menu-left">
          <!-- Main menu items grouped by category -->
          <div class="menu-section" v-for="(group, groupName) in groupedMenuItems" :key="groupName">
            <div class="menu-section-header" v-if="groupName !== 'main'">
              <i :class="getGroupIcon(groupName)"></i>
              <span>{{ getGroupTitle(groupName) }}</span>
            </div>
            <div class="menu-section-items">
              <div
                v-for="(item, itemKey) in group"
                :key="itemKey"
                class="app-tile"
                :data-app="itemKey"
                @click="openApp(itemKey)"
              >
                <i :class="item.icon"></i>
                <span>{{ item.title }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="start-menu-footer">
        <button class="power-button" @click="handleLogout">
          <i class="fas fa-power-off"></i>
        </button>
      </div>
    </div>

    <!-- Battery Dropdown -->
    <div 
      class="battery-dropdown" 
      :class="{ active: batteryDropdownActive }"
      :style="getBatteryDropdownStyle()"
      @click.stop
      ref="batteryDropdownRef"
    >
      <div class="battery-info">
        <div class="battery-percentage">90%</div>
        <div class="battery-days">300 روز مانده</div>
      </div>
    </div>

    <!-- Notification Dropdown -->
    <div 
      class="notification-dropdown" 
      :class="{ active: notificationDropdownActive }"
      :style="getNotificationDropdownStyle()"
      @click.stop
      ref="notificationDropdownRef"
    >
      <div class="notification-dropdown-header">
        <h3>اعلان‌ها</h3>
        <button 
          v-if="notifications.length > 0" 
          class="clear-all-btn"
          @click="markAllAsRead"
        >
          همه را خوانده شده علامت بزن
        </button>
      </div>
      <div class="notification-dropdown-content">
        <div v-if="notifications.length === 0" class="no-notifications">
          <i class="fas fa-bell-slash"></i>
          <p>اعلانی وجود ندارد</p>
        </div>
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification-item"
          :class="{ unread: !notification.read }"
          @click="openNotification(notification)"
        >
          <div class="notification-icon-wrapper">
            <i :class="getNotificationIcon(notification.type)"></i>
          </div>
          <div class="notification-content">
            <div class="notification-title">{{ notification.title }}</div>
            <div class="notification-preview">{{ getNotificationPreview(notification.message) }}</div>
            <div class="notification-time">{{ formatNotificationTime(notification.createdAt) }}</div>
          </div>
          <div v-if="!notification.read" class="notification-unread-dot"></div>
        </div>
      </div>
    </div>

    <!-- Context Menu -->
    <div 
      class="context-menu" 
      :class="{ active: contextMenuActive }"
      :style="{ left: contextMenuPosition.x + 'px', top: contextMenuPosition.y + 'px' }"
      @click.stop
    >
      <div class="context-menu-item" @click="handleContextAction('open')">
        <i class="fas fa-folder-open"></i>
        <span>باز کردن</span>
      </div>
      <div class="context-menu-item" @click="handleContextAction('rename')">
        <i class="fas fa-edit"></i>
        <span>تغییر نام</span>
      </div>
      <div class="context-menu-item" @click="handleContextAction('delete')">
        <i class="fas fa-trash"></i>
        <span>حذف</span>
      </div>
      <div class="context-menu-divider"></div>
      <div class="context-menu-item" @click="handleContextAction('properties')">
        <i class="fas fa-info-circle"></i>
        <span>خصوصیات</span>
      </div>
    </div>

    <!-- Slot for page content (hidden, windows will show content) -->
    <div style="display: none;">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '~/components/useAuth'
import WindowPageLoader from '~/components/WindowPageLoader.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuth()

const {
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
  apps: windowsApps,
  menuItems,
  initializeDateTime,
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
} = useWindows()

const desktopIconsRef = ref(null)
const notificationIconRef = ref(null)
const notificationDropdownRef = ref(null)
const batteryIconRef = ref(null)
const batteryDropdownRef = ref(null)

// Icon positions
const iconPositions = ref({})
const iconDragging = ref({ appName: null, startX: 0, startY: 0, offsetX: 0, offsetY: 0 })

// Notification state
const notificationDropdownActive = ref(false)

// Battery state
const batteryDropdownActive = ref(false)

// Volume/Mute state - using composable
const { isMuted, toggleMute, loadMuteState } = useSystemMute()

// Sample notifications data
const notifications = ref([
  {
    id: 1,
    title: 'فاکتور جدید ثبت شد',
    message: 'فاکتور شماره 12345 با مبلغ 1,500,000 تومان با موفقیت ثبت شد. لطفا برای بررسی و تایید اقدام کنید.',
    type: 'info',
    read: false,
    createdAt: new Date(Date.now() - 5 * 60 * 1000) // 5 minutes ago
  },
  {
    id: 2,
    title: 'هشدار موجودی کم',
    message: 'محصول "لپ تاپ Dell" موجودی کمی دارد. موجودی فعلی: 3 عدد. لطفا برای سفارش مجدد اقدام کنید.',
    type: 'warning',
    read: false,
    createdAt: new Date(Date.now() - 30 * 60 * 1000) // 30 minutes ago
  },
  {
    id: 3,
    title: 'درخواست پشتیبانی',
    message: 'یک درخواست پشتیبانی جدید از طرف کاربر "علی احمدی" دریافت شده است. موضوع: مشکل در ثبت سفارش',
    type: 'info',
    read: true,
    createdAt: new Date(Date.now() - 2 * 60 * 60 * 1000) // 2 hours ago
  },
  {
    id: 4,
    title: 'پرداخت موفق',
    message: 'پرداخت فاکتور شماره 12340 با مبلغ 2,300,000 تومان با موفقیت انجام شد. رسید پرداخت برای مشتری ارسال شد.',
    type: 'success',
    read: false,
    createdAt: new Date(Date.now() - 3 * 60 * 60 * 1000) // 3 hours ago
  },
  {
    id: 5,
    title: 'به‌روزرسانی سیستم',
    message: 'به‌روزرسانی جدید سیستم در دسترس است. نسخه 2.1.0 شامل بهبودهای عملکردی و رفع باگ‌های امنیتی می‌باشد.',
    type: 'info',
    read: true,
    createdAt: new Date(Date.now() - 24 * 60 * 60 * 1000) // 1 day ago
  }
])

// Computed unread count
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

// Group menu items by parent
const groupedMenuItems = computed(() => {
  const groups = {
    main: {},
    users: {},
    products: {},
    accounting: {},
    system: {}
  }
  
  Object.entries(menuItems).forEach(([key, item]) => {
    if (!item.parent) {
      groups.main[key] = item
    } else if (item.parent === 'users') {
      groups.users[key] = item
    } else if (item.parent === 'products') {
      groups.products[key] = item
    } else if (item.parent === 'accounting') {
      groups.accounting[key] = item
    } else if (item.parent === 'system') {
      groups.system[key] = item
    }
  })
  
  // Remove empty groups
  Object.keys(groups).forEach(key => {
    if (Object.keys(groups[key]).length === 0) {
      delete groups[key]
    }
  })
  
  return groups
})

// Get group icon
const getGroupIcon = (groupName) => {
  const icons = {
    users: 'fa fa-users',
    products: 'fa fa-tags',
    accounting: 'fa fa-file',
    system: 'fa fa-cog'
  }
  return icons[groupName] || 'fa fa-folder'
}

// Get group title
const getGroupTitle = (groupName) => {
  const titles = {
    users: 'کاربران',
    products: 'محصولات',
    accounting: 'عملیات حسابداری',
    system: 'سیستم'
  }
  return titles[groupName] || groupName
}

// Get desktop bounds (considering taskbar)
const getDesktopBounds = () => {
  if (typeof window === 'undefined') {
    return { width: 1920, height: 1080 }
  }
  const taskbarHeight = 50
  return {
    width: window.innerWidth,
    height: window.innerHeight - taskbarHeight
  }
}

// Constrain icon position to desktop bounds
const constrainIconPosition = (x, y) => {
  const bounds = getDesktopBounds()
  const iconWidth = 80
  const iconHeight = 100
  const padding = 5 // Small padding from edges
  
  return {
    x: Math.max(padding, Math.min(x, bounds.width - iconWidth - padding)),
    y: Math.max(padding, Math.min(y, bounds.height - iconHeight - padding))
  }
}

// Calculate initial grid position for icons (Windows-style: multiple columns)
const getInitialIconPosition = (index) => {
  if (typeof window === 'undefined') {
    return { x: 20, y: 20 }
  }
  const bounds = getDesktopBounds()
  const iconWidth = 80
  const iconHeight = 100
  const padding = 20
  const spacing = 10 // Space between icons
  
  // Calculate how many columns fit in the desktop width
  const availableWidth = bounds.width - (padding * 2)
  const iconsPerRow = Math.floor(availableWidth / (iconWidth + spacing))
  const colsPerRow = Math.max(1, iconsPerRow) // At least 1 column
  
  // Calculate row and column for this icon
  const row = Math.floor(index / colsPerRow)
  const col = index % colsPerRow
  
  // Windows-style: icons start from top-left, arranged in columns
  const position = {
    x: padding + (col * (iconWidth + spacing)),
    y: padding + (row * (iconHeight + spacing))
  }
  
  // Ensure initial position is within bounds
  return constrainIconPosition(position.x, position.y)
}

const getIconStyle = (appName, index) => {
  const pos = iconPositions.value[appName]
  if (pos) {
    // Ensure saved position is within bounds
    const constrainedPos = constrainIconPosition(pos.x, pos.y)
    // Update if position was constrained
    if (constrainedPos.x !== pos.x || constrainedPos.y !== pos.y) {
      iconPositions.value[appName] = constrainedPos
    }
    return {
      left: `${constrainedPos.x}px`,
      top: `${constrainedPos.y}px`,
      position: 'absolute'
    }
  }
  // Initial grid position
  const initialPos = getInitialIconPosition(index)
  return {
    left: `${initialPos.x}px`,
    top: `${initialPos.y}px`,
    position: 'absolute'
  }
}

const selectIcon = (appName) => {
  if (!isDraggingIcon.value && iconDragging.value.appName !== appName) {
    selectedIcon.value = appName
  }
}

// Icon dragging
const startIconDrag = (event, appName, index) => {
  if (event.button !== 0) return // Only left click
  
  event.preventDefault()
  event.stopPropagation()
  
  isDraggingIcon.value = true
  iconDragging.value.appName = appName
  currentDraggedIcon.value = appName
  
  const icon = event.currentTarget
  const rect = icon.getBoundingClientRect()
  const desktopRect = desktopIconsRef.value?.getBoundingClientRect() || { left: 0, top: 0 }
  
  const currentPos = iconPositions.value[appName] || getInitialIconPosition(index)
  
  iconDragging.value.startX = event.clientX
  iconDragging.value.startY = event.clientY
  iconDragging.value.offsetX = event.clientX - desktopRect.left - currentPos.x
  iconDragging.value.offsetY = event.clientY - desktopRect.top - currentPos.y
}

const handleIconMouseMove = (event) => {
  if (typeof window === 'undefined') return
  
  if (isDraggingIcon.value && iconDragging.value.appName) {
    const desktopRect = desktopIconsRef.value?.getBoundingClientRect()
    if (!desktopRect) return
    
    const appName = iconDragging.value.appName
    
    let x = event.clientX - desktopRect.left - iconDragging.value.offsetX
    let y = event.clientY - desktopRect.top - iconDragging.value.offsetY
    
    // Constrain to desktop bounds
    const constrainedPos = constrainIconPosition(x, y)
    
    // Update position
    if (!iconPositions.value[appName]) {
      iconPositions.value[appName] = { x: 0, y: 0 }
    }
    iconPositions.value[appName].x = constrainedPos.x
    iconPositions.value[appName].y = constrainedPos.y
  }
}

const handleIconMouseUp = () => {
  if (isDraggingIcon.value && iconDragging.value.appName) {
    // Save position to localStorage
    const appName = iconDragging.value.appName
    const pos = iconPositions.value[appName]
    if (pos) {
      saveIconPosition(appName, pos.x, pos.y)
    }
  }
  
  isDraggingIcon.value = false
  iconDragging.value.appName = null
  currentDraggedIcon.value = null
}

const handleContextMenu = (event, appName) => {
  event.preventDefault()
  event.stopPropagation()
  showContextMenu(event.clientX, event.clientY, appName)
}

// Prevent default context menu everywhere except on icons
const handleGlobalContextMenu = (event) => {
  // If clicking on desktop icon, let the icon's handler manage it
  if (event.target.closest('.desktop-icon')) {
    // The icon's handler will prevent default and show custom menu
    return
  }
  // For all other places, prevent default browser context menu
  event.preventDefault()
  event.stopPropagation()
}

// Window styles
const getWindowStyle = (window) => {
  const style = {
    zIndex: window.zIndex || 100
  }
  
  if (window.minimized) {
    // Hide minimized windows but keep them in DOM to preserve iframe state
    style.visibility = 'hidden'
    style.pointerEvents = 'none'
    style.opacity = '0'
    // Keep dimensions to prevent reflow
    style.width = window.width ? `${window.width}px` : '1000px'
    style.height = window.height ? `${window.height}px` : '700px'
    if (window.x !== undefined) style.left = `${window.x}px`
    if (window.y !== undefined) style.top = `${window.y}px`
  } else if (window.maximized) {
    style.width = '100%'
    style.height = 'calc(100vh - 50px)'
    style.left = '0'
    style.top = '0'
    style.display = 'flex'
    style.visibility = 'visible'
  } else {
    style.width = window.width ? `${window.width}px` : '1000px'
    style.height = window.height ? `${window.height}px` : '700px'
    if (window.x !== undefined) style.left = `${window.x}px`
    if (window.y !== undefined) style.top = `${window.y}px`
    style.display = 'flex'
    style.visibility = 'visible'
  }
  
  return style
}

// Window dragging
const startDrag = (event, windowId) => {
  if (event.target.closest('.window-controls')) return
  
  isDragging.value = true
  const window = openWindows.value.find(w => w.id === windowId)
  if (!window) return
  
  currentDraggedWindow.value = window
  
  const rect = event.currentTarget.getBoundingClientRect()
  dragOffset.value = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  }
}

const handleMouseMove = (event) => {
  if (isDragging.value && currentDraggedWindow.value) {
    const window = currentDraggedWindow.value
    window.x = Math.max(0, event.clientX - dragOffset.value.x)
    window.y = Math.max(0, event.clientY - dragOffset.value.y)
  }
  // Handle icon dragging
  handleIconMouseMove(event)
}

const handleMouseUp = () => {
  isDragging.value = false
  currentDraggedWindow.value = null
  // Handle icon drag end
  handleIconMouseUp()
}

// Logout
const handleLogout = () => {
  if (confirm('آیا می‌خواهید از سیستم خارج شوید؟')) {
    authStore.logout()
    router.push('/login')
  }
}

// Battery functions
const toggleBatteryDropdown = () => {
  batteryDropdownActive.value = !batteryDropdownActive.value
  // Close notification dropdown if open
  if (batteryDropdownActive.value) {
    notificationDropdownActive.value = false
  }
}

const getBatteryDropdownStyle = () => {
  if (typeof window === 'undefined' || !batteryIconRef.value) {
    return {}
  }
  
  const iconRect = batteryIconRef.value.getBoundingClientRect()
  const dropdownWidth = 180
  const taskbarHeight = 50
  
  // Position dropdown above the taskbar, aligned to the right (RTL)
  return {
    right: `${window.innerWidth - iconRect.right}px`,
    bottom: `${taskbarHeight + 5}px`,
    width: `${dropdownWidth}px`
  }
}

// Notification functions
const toggleNotificationDropdown = () => {
  notificationDropdownActive.value = !notificationDropdownActive.value
  // Close battery dropdown if open
  if (notificationDropdownActive.value) {
    batteryDropdownActive.value = false
  }
}

const getNotificationDropdownStyle = () => {
  if (typeof window === 'undefined' || !notificationIconRef.value) {
    return {}
  }
  
  const iconRect = notificationIconRef.value.getBoundingClientRect()
  const dropdownWidth = 380
  const dropdownHeight = 500
  const taskbarHeight = 50
  
  // Position dropdown above the taskbar, aligned to the right (RTL)
  return {
    right: `${window.innerWidth - iconRect.right}px`,
    bottom: `${taskbarHeight + 5}px`,
    width: `${dropdownWidth}px`,
    maxHeight: `${dropdownHeight}px`
  }
}

const getNotificationIcon = (type) => {
  const icons = {
    info: 'fas fa-info-circle',
    warning: 'fas fa-exclamation-triangle',
    success: 'fas fa-check-circle',
    error: 'fas fa-times-circle'
  }
  return icons[type] || 'fas fa-bell'
}

const getNotificationPreview = (message) => {
  if (message.length > 60) {
    return message.substring(0, 60) + '...'
  }
  return message
}

const formatNotificationTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'همین الان'
  if (minutes < 60) return `${minutes} دقیقه پیش`
  if (hours < 24) return `${hours} ساعت پیش`
  if (days < 7) return `${days} روز پیش`
  
  return date.toLocaleDateString('fa-IR')
}

const openNotification = (notification) => {
  // Mark as read
  notification.read = true
  
  // Close dropdown
  notificationDropdownActive.value = false
  
  // Open notification in a window
  const windowId = `notification-${notification.id}`
  
  // Check if window already exists
  const existingWindow = openWindows.value.find(w => w.id === windowId)
  if (existingWindow) {
    focusWindow(windowId)
    return
  }
  
  // Create notification content HTML
  const notificationContent = `
    <div class="notification-window-content" style="padding: 20px; direction: rtl; text-align: right;">
      <div class="notification-header" style="border-bottom: 2px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
          <i class="${getNotificationIcon(notification.type)}" style="font-size: 24px; color: ${getNotificationColor(notification.type)};"></i>
          <h2 style="margin: 0; font-size: 20px; font-weight: bold;">${notification.title}</h2>
        </div>
        <div style="color: #666; font-size: 14px;">
          <i class="fas fa-clock"></i> ${formatNotificationTime(notification.createdAt)}
        </div>
      </div>
      <div class="notification-body" style="line-height: 1.8; font-size: 16px; color: #333;">
        ${notification.message}
      </div>
    </div>
  `
  
  // Calculate window position (centered)
  const windowWidth = 600
  const windowHeight = 400
  const x = typeof window !== 'undefined' ? Math.max(0, (window.innerWidth - windowWidth) / 2) : 100
  const y = typeof window !== 'undefined' ? Math.max(0, (window.innerHeight - windowHeight) / 2) : 100
  
  // Create new window object
  const newWindow = {
    id: windowId,
    title: notification.title,
    icon: getNotificationIcon(notification.type),
    content: notificationContent,
    width: windowWidth,
    height: windowHeight,
    x: x,
    y: y,
    minimized: false,
    maximized: false,
    zIndex: 0 // Will be set by focusWindow
  }
  
  // Add window to openWindows
  openWindows.value.push(newWindow)
  
  // Focus the window (this will set the z-index properly)
  focusWindow(windowId)
}

const getNotificationColor = (type) => {
  const colors = {
    info: '#2196F3',
    warning: '#FF9800',
    success: '#4CAF50',
    error: '#F44336'
  }
  return colors[type] || '#2196F3'
}

const markAllAsRead = () => {
  notifications.value.forEach(n => {
    n.read = true
  })
}

// Close start menu on outside click
const handleClickOutside = (event) => {
  if (!event.target.closest('.start-menu') && 
      !event.target.closest('.start-button')) {
    closeStartMenu()
  }
  if (!event.target.closest('.context-menu')) {
    hideContextMenu()
  }
  if (!event.target.closest('.notification-dropdown') && 
      !event.target.closest('.notification-icon')) {
    notificationDropdownActive.value = false
  }
  if (!event.target.closest('.battery-dropdown') && 
      !event.target.closest('.battery-icon')) {
    batteryDropdownActive.value = false
  }
  if (!event.target.closest('.desktop-icon')) {
    selectedIcon.value = null
  }
}

// Initialize
onMounted(() => {
  if (typeof window !== 'undefined') {
    initializeDateTime()
    
    // Load mute state from localStorage
    loadMuteState()
    
    // Load saved icon positions
    const savedPositions = getIconPositions()
    // Constrain saved positions to desktop bounds
    const constrainedPositions = {}
    Object.keys(savedPositions).forEach(key => {
      const pos = savedPositions[key]
      if (pos && typeof pos.x === 'number' && typeof pos.y === 'number') {
        constrainedPositions[key] = constrainIconPosition(pos.x, pos.y)
        // Save corrected position if it was changed
        if (constrainedPositions[key].x !== pos.x || constrainedPositions[key].y !== pos.y) {
          saveIconPosition(key, constrainedPositions[key].x, constrainedPositions[key].y)
        }
      }
    })
    iconPositions.value = constrainedPositions
    
    // Initialize positions for icons that don't have saved positions
    const menuItemsArray = Object.keys(menuItems)
    menuItemsArray.forEach((itemKey, index) => {
      if (!iconPositions.value[itemKey]) {
        const initialPos = getInitialIconPosition(index)
        iconPositions.value[itemKey] = initialPos
        saveIconPosition(itemKey, initialPos.x, initialPos.y)
      }
    })
    
    document.addEventListener('mousemove', handleMouseMove)
    document.addEventListener('mouseup', handleMouseUp)
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('contextmenu', handleGlobalContextMenu)
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    document.removeEventListener('mousemove', handleMouseMove)
    document.removeEventListener('mouseup', handleMouseUp)
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('contextmenu', handleGlobalContextMenu)
  }
})

useHead({
  title: computed(() => {
    const pageTitle = route.meta?.title
    return pageTitle ? `پنل مدیریت | ${pageTitle}` : 'پنل مدیریت'
  }),
  link: [
    { rel: 'stylesheet', href: '/windows/css/style.css' },
    { rel: 'stylesheet', href: '/windows/fonts/webfonts/css/all.min.css' }
  ]
})

// Define layout meta
definePageMeta({
  middleware: 'auth'
})
</script>

<style scoped>
.windows-layout {
  height: 100vh;
  overflow: hidden;
}

.window-content {
  overflow: hidden;
  overflow-y: auto;
}

/* Tray Icon Styles */
.tray-icon {
  cursor: pointer;
  transition: background-color 0.2s;
}

.tray-icon:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.tray-icon.active {
  background-color: rgba(255, 255, 255, 0.15);
}

.notification-icon {
  position: relative;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background-color: #f44336;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: bold;
  border: 2px solid #1e1e1e;
}

.notification-icon.hasUnread::before {
  content: '';
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  background-color: #4CAF50;
  border-radius: 50%;
  border: 1px solid #1e1e1e;
}

/* Notification Dropdown Styles */
.notification-dropdown {
  position: fixed;
  background-color: #2d2d2d;
  border: 1px solid #404040;
  border-radius: 8px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
  z-index: 10000;
  display: none;
  flex-direction: column;
  overflow: hidden;
  direction: rtl;
}

.notification-dropdown.active {
  display: flex;
}

.notification-dropdown-header {
  padding: 15px 20px;
  border-bottom: 1px solid #404040;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #252525;
}

.notification-dropdown-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
}

.clear-all-btn {
  background: none;
  border: none;
  color: #4CAF50;
  cursor: pointer;
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.clear-all-btn:hover {
  background-color: rgba(76, 175, 80, 0.1);
}

.notification-dropdown-content {
  overflow-y: auto;
  max-height: 450px;
}

.no-notifications {
  padding: 40px 20px;
  text-align: center;
  color: #888;
}

.no-notifications i {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.no-notifications p {
  margin: 0;
  font-size: 14px;
}

.notification-item {
  padding: 15px 20px;
  border-bottom: 1px solid #404040;
  cursor: pointer;
  display: flex;
  gap: 15px;
  align-items: flex-start;
  transition: background-color 0.2s;
  position: relative;
}

.notification-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.notification-item.unread {
  background-color: rgba(76, 175, 80, 0.05);
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon-wrapper {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notification-icon-wrapper i {
  font-size: 18px;
  color: #4CAF50;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  font-size: 14px;
  color: #fff;
  margin-bottom: 5px;
}

.notification-preview {
  font-size: 13px;
  color: #aaa;
  margin-bottom: 8px;
  line-height: 1.5;
}

.notification-time {
  font-size: 11px;
  color: #666;
}

.notification-unread-dot {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background-color: #4CAF50;
  border-radius: 50%;
}

/* Notification Window Content Styles */
.notification-window-content {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Battery Dropdown Styles */
.battery-dropdown {
  position: fixed;
  background-color: #2d2d2d;
  border: 1px solid #404040;
  border-radius: 6px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  z-index: 10000;
  display: none;
  direction: rtl;
  padding: 12px 16px;
}

.battery-dropdown.active {
  display: block;
}

.battery-info {
  text-align: center;
  color: #fff;
}

.battery-percentage {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #4CAF50;
}

.battery-days {
  font-size: 13px;
  color: #aaa;
}

/* Volume Icon Styles */
.volume-icon.muted {
  opacity: 0.7;
}

.volume-icon.muted i {
  color: #f44336;
}

</style>
