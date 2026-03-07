import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    proxy: {
      '/api/products': { target: 'http://localhost:8001', changeOrigin: true },
      '/api/inventory': { target: 'http://localhost:8002', changeOrigin: true },
      '/api/orders': { target: 'http://localhost:8003', changeOrigin: true },
      '/api/users': { target: 'http://localhost:8004', changeOrigin: true },
    },
  },
})
