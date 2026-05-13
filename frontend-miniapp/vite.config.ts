import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [react()],
        base: env.FRONTEND_MINIAPP_PATH || '/',
        server: {
            host: '0.0.0.0',
            port: 3000,
        },
        build: {
            outDir: 'dist',
            assetsDir: 'assets',
            sourcemap: false,
            minify: 'esbuild',
            target: 'es2015',
            reportCompressedSize: false,
            rollupOptions: {
                output: {
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash].[ext]',
                    manualChunks: {
                        'react-vendor': ['react', 'react-dom'],
                        'axios-vendor': ['axios'],
                    },
                },
            },
        },
        optimizeDeps: {
            exclude: ['lucide-react'],
        },
        define: {
            __APP_NAME__: JSON.stringify(env.VITE_APP_NAME || 'VPN Aginskoe'),
            __BOT_USERNAME__: JSON.stringify(env.VITE_TELEGRAM_BOT_USERNAME || 'vpn-aginskoe'),
        },
    };
});