import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],

  // for exposing the service in the network:
  server: {
    host: '0.0.0.0'
  }
})
