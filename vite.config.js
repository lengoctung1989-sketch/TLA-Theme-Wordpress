// P6.1 build — Vite: output manifest.json cho Enqueue (P2.2) đọc.
import { defineConfig } from 'vite';
import { resolve } from 'node:path';

// Nguồn asset và đích build. base khớp đường dẫn theme trong WordPress.
const src = resolve(import.meta.dirname, 'assets/src');

export default defineConfig(({ command }) => ({
    root: src,
    // Dev server phục vụ từ gốc (Enqueue.php nạp http://127.0.0.1:5173/main.js);
    // bản build dùng đường dẫn theme thật để trỏ vào assets/dist.
    base: command === 'serve' ? '/' : '/wp-content/themes/tungleads-theme/assets/dist/',
    build: {
        manifest: true,
        outDir: resolve(import.meta.dirname, 'assets/dist'),
        emptyOutDir: true,
        rollupOptions: {
            input: {
                // Tách theo site mode: 'main' luôn nạp; 'woocommerce' chỉ nạp khi P4 boot.
                main: resolve(src, 'main.js'),
                woocommerce: resolve(src, 'woocommerce.js'),
            },
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        cors: true,
    },
}));
