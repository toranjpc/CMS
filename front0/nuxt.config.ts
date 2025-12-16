// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  app: {
    head: {
      link: [
        { rel: 'stylesheet', href: '/css/bootstrap.rtl.min.css' },
        { rel: 'stylesheet', href: '/css/adminStyle.css' }
      ],
      script: [
        { src: '/js/bootstrap.bundle.min.js', defer: true }
      ]
    }
  },
  modules: ['@pinia/nuxt'],
})
