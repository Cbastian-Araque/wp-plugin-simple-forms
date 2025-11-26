import { defineConfig } from 'vite';
import path from 'path';
import fs from 'fs';

const jsDir = path.resolve(__dirname, 'assets/src/js');

const inputs = fs.readdirSync(jsDir)
  .filter(file => file.endsWith('.js'))
  .reduce((entries, file) => {
    const name = file.replace('.js', '');
    entries[name] = path.resolve(jsDir, file);
    return entries;
  }, {});

export default defineConfig({
  root: 'assets/src/js',
  build: {
    outDir: '../../dist',
    emptyOutDir: true,
    rollupOptions: {
      input: inputs,
      output: {
        entryFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      },
    },
  },
});
