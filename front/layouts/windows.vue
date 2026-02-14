<template>
  <div dir="rtl" lang="fa" class="windows-layout">
    <!-- Desktop Background -->
    <div class="desktop">
      <!-- Desktop Icons -->
      <div class="desktop-icons" ref="desktopIconsRef">
        <div 
          v-for="(app, appName, index) in windowsApps" 
          :key="appName"
          class="desktop-icon"
          :class="{ 
            selected: selectedIcon === appName, 
            dragging: isDraggingIcon && iconDragging.appName === appName 
          }"
          :data-app="appName"
          :style="getIconStyle(appName, index)"
          @dblclick="openApp(appName)"
          @mousedown="startIconDrag($event, appName, index)"
          @click="selectIcon(appName)"
          @contextmenu.prevent="handleContextMenu($event, appName)"
        >
          <div class="icon-image">
            <i :class="app.icon"></i>
          </div>
          <span class="icon-label">{{ app.title }}</span>
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
              <button class="window-control maximize" @click="maximizeWindow(window.id)" title="بزرگ کردن">
                <i class="fas fa-square"></i>
              </button>
              <button class="window-control close" @click="closeWindow(window.id)" title="بستن">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="window-content" v-if="!window.minimized" v-html="window.content"></div>
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
            :class="{ active: window.zIndex === windowZIndex }"
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
        <div class="start-menu-apps">
          <div
            v-for="(app, appName) in windowsApps"
            :key="appName"
            class="app-tile"
            :data-app="appName"
            @click="openApp(appName)"
          >
            <i :class="app.icon"></i>
            <span>{{ app.title }}</span>
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

// Calculate initial grid position for icons
const getInitialIconPosition = (index) => {
  if (typeof window === 'undefined') {
    return { x: 20, y: 20 }
  }
  const iconWidth = 80
  const iconHeight = 100
  const padding = 20
  const screenWidth = window.innerWidth || 1920
  const colsPerRow = Math.floor((screenWidth - padding * 2) / iconWidth)
  const row = Math.floor(index / colsPerRow)
  const col = index % colsPerRow
  return {
    x: padding + (col * iconWidth),
    y: padding + (row * iconHeight)
  }
}

const getIconStyle = (appName, index) => {
  const pos = iconPositions.value[appName]
  if (pos) {
    return {
      left: `${pos.x}px`,
      top: `${pos.y}px`,
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
    const desktopRect = desktopIconsRef.value?.getBoundingClientRect() || { 
      left: 0, 
      top: 0, 
      width: window.innerWidth, 
      height: window.innerHeight - 50 
    }
    const appName = iconDragging.value.appName
    
    let x = event.clientX - desktopRect.left - iconDragging.value.offsetX
    let y = event.clientY - desktopRect.top - iconDragging.value.offsetY
    
    // Constrain to desktop bounds
    const iconWidth = 80
    const iconHeight = 100
    x = Math.max(0, Math.min(x, desktopRect.width - iconWidth))
    y = Math.max(0, Math.min(y, desktopRect.height - iconHeight))
    
    // Update position
    if (!iconPositions.value[appName]) {
      iconPositions.value[appName] = { x: 0, y: 0 }
    }
    iconPositions.value[appName].x = x
    iconPositions.value[appName].y = y
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
  showContextMenu(event.clientX, event.clientY, appName)
}

// Window styles
const getWindowStyle = (window) => {
  const style = {
    zIndex: window.zIndex || 100
  }
  
  if (window.maximized) {
    style.width = '100%'
    style.height = 'calc(100vh - 50px)'
    style.left = '0'
    style.top = '0'
  } else if (!window.minimized) {
    style.width = '800px'
    style.height = '600px'
    if (window.x !== undefined) style.left = `${window.x}px`
    if (window.y !== undefined) style.top = `${window.y}px`
  } else {
    style.display = 'none'
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
    iconPositions.value = savedPositions
    
    // Initialize positions for icons that don't have saved positions
    const appsArray = Object.keys(windowsApps)
    appsArray.forEach((appName, index) => {
      if (!iconPositions.value[appName]) {
        const initialPos = getInitialIconPosition(index)
        iconPositions.value[appName] = initialPos
        saveIconPosition(appName, initialPos.x, initialPos.y)
      }
    })
    
    document.addEventListener('mousemove', handleMouseMove)
    document.addEventListener('mouseup', handleMouseUp)
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    document.removeEventListener('mousemove', handleMouseMove)
    document.removeEventListener('mouseup', handleMouseUp)
    document.removeEventListener('click', handleClickOutside)
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
</style>
