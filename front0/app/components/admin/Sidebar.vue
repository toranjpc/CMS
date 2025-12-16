<template>
  <aside :class="{ collapsed: collapsed }" id="sidebar">
    <ul class="sidebar-menu">
      <li v-for="(item, index) in menuItems" :key="index">
        <a
          :href="item.url"
          :class="{ active: item.url === activeUrl }"
          @click.prevent="toggleSubmenu(index)"
        >
          {{ item.label }}
        </a>
        <ul v-if="item.submenu" :class="{ open: item.open }" class="submenu">
          <li v-for="(sub, i) in item.submenu" :key="i">
            <a :href="sub.url">{{ sub.label }}</a>
          </li>
        </ul>
      </li>
    </ul>
  </aside>
</template>

<script setup lang="ts">
import { ref, reactive, watch, defineProps, defineEmits } from 'vue'

const props = defineProps<{
  collapsed: boolean
  activeUrl: string
}>()

const emit = defineEmits<{
  (e: 'toggle-submenu', index: number): void
}>()

const menuItems = reactive([
  { label: 'Dashboard', url: '/dashboard', submenu: null, open: false },
  {
    label: 'Users',
    url: '#',
    submenu: [
      { label: 'All Users', url: '/users' },
      { label: 'Add User', url: '/users/add' },
    ],
    open: false,
  },
  // ... ادامه منوها
])

function toggleSubmenu(index: number) {
  menuItems.forEach((item, i) => {
    if (i !== index) item.open = false
  })
  menuItems[index].open = !menuItems[index].open
  emit('toggle-submenu', index)
}
</script>

<style>
#sidebar {
  width: 250px;
  transition: all 0.3s;
}
#sidebar.collapsed {
  width: 80px;
}
.submenu {
  display: none;
}
.submenu.open {
  display: block;
}
a.active {
  font-weight: bold;
}
</style>
