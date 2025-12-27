import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    port: 3000,
    hmr: {
      clientPort: 8080,
    },
    proxy: {
      '/api': {
        target: 'https://beautybook.ubiz.ru',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
