<template>
  <n-layout has-sider>
    <n-layout-sider
      width="240"
      bordered
      collapse-mode="width"
      :collapsed-width="60"
    >
      <Sidebar />
    </n-layout-sider>

    <n-layout>
      <n-layout-header bordered>
        <Header />
      </n-layout-header>

      <n-layout-content>
        <slot />
      </n-layout-content>
    </n-layout>
  </n-layout>
</template>

<script setup>
import Sidebar from '~/components/Sidebar.vue'
import Header from '~/components/Header.vue'
</script>
