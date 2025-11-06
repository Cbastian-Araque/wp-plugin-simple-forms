import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  root: 'assets/src/js',
  build: {
    outDir: '../../dist',
    emptyOutDir: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'assets/src/js/main.js'),
      output: {
        entryFileNames: 'build.js',
        assetFileNames: 'build.[ext]',
      },
    },
  },
});
