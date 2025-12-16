<template>
  <nav id="navbar">
    <button id="toggleMenu" @click="$emit('toggle-menu')">
      ☰
    </button>
  </nav>
</template>

<script setup lang="ts">
</script>

<style>
#navbar {
  height: 60px;
  background: #f8f9fa;
}
</style>
