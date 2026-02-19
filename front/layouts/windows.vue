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
          <div class="tray-icon">
            <i class="fas fa-wifi"></i>
          </div>
          <div class="tray-icon">
            <i class="fas fa-volume-up"></i>
          </div>
          <div class="tray-icon">
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

// Icon positions
const iconPositions = ref({})
const iconDragging = ref({ appName: null, startX: 0, startY: 0, offsetX: 0, offsetY: 0 })

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

// Close start menu on outside click
const handleClickOutside = (event) => {
  if (!event.target.closest('.start-menu') && 
      !event.target.closest('.start-button')) {
    closeStartMenu()
  }
  if (!event.target.closest('.context-menu')) {
    hideContextMenu()
  }
  if (!event.target.closest('.desktop-icon')) {
    selectedIcon.value = null
  }
}

// Initialize
onMounted(() => {
  if (typeof window !== 'undefined') {
    initializeDateTime()
    
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

</style>
