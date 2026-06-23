import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';
import path from 'node:path';

function collectEntries(rootDir) {
  const entries = [];

  function walk(dir) {
    for (const item of fs.readdirSync(dir, { withFileTypes: true })) {
      const full = path.join(dir, item.name);
      if (item.isDirectory()) {
        walk(full);
        continue;
      }
      if (!item.isFile()) continue;

      const rel = full.replace(/\\/g, '/');
      if (rel.endsWith('.js') || rel.endsWith('.css')) {
        entries.push(rel);
      }
    }
  }

  walk(rootDir);
  return entries;
}

export default defineConfig({
  plugins: [
    laravel({
      input: collectEntries('resources'),
      refresh: true,
    }),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0',
    port: 5174,
    hmr: {
      host: 'm2obrasostenibles.es',
      protocol: 'wss',
    },
  },
});

