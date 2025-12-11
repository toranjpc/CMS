<template>
  <n-layout has-sider>
    <n-layout-sider width="220">
      <VendorSidebar />
    </n-layout-sider>

    <n-layout-content>
      <slot />
    </n-layout-content>
  </n-layout>
</template>

<script setup>
import VendorSidebar from '~/components/VendorSidebar.vue'
</script>
