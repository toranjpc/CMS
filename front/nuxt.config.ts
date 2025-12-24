// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: false, // SPA mode for shared hosting

  app: {
    head: {
      htmlAttrs: { lang: 'fa', dir: 'rtl' },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }
      ],
      link: [
        { rel: 'stylesheet', href: '/css/bootstrap.rtl.min.css' },
        { rel: 'stylesheet', href: '/css/adminStyle.css' },
        { rel: 'stylesheet', href: '/fonts/font-awesome.min.css' },
        // { rel: 'stylesheet', href: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css' }
      ],
      script: [
        { src: '/js/bootstrap.bundle.min.js', defer: true },
        // { src: '/js/popper.min.js', defer: true },
        // { src: '/js/main.js', defer: true }
      ]
    }
  },


  runtimeConfig: {
    public: {
      apiBase: 'http://pet.na/'
    }
  }
})
