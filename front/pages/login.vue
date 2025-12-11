<template>
  <n-card title="ورود" style="max-width:400px;margin:50px auto;">
    <n-form @submit.prevent="submit">
      <n-form-item label="ایمیل">
        <n-input v-model="email" />
      </n-form-item>

      <n-form-item label="رمز عبور">
        <n-input type="password" v-model="password" />
      </n-form-item>

      <n-button type="primary" block html-type="submit">ورود</n-button>
    </n-form>
  </n-card>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('')
const password = ref('')

const submit = () => {
  console.log('login...', email.value, password.value)
}
</script>

<script>
export default {
  layout: 'public'
}
</script>
