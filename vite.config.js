import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { copyFileSync, existsSync, mkdirSync, readdirSync, statSync } from 'fs';
import { join } from 'path';

// Plugin personnalisé pour copier les images
function copyImagesPlugin() {
    return {
        name: 'copy-images',
        buildStart() {
            const sourceDir = 'resources/images';
            const targetDir = 'public/images';
            
            function copyDirectory(src, dest) {
                if (!existsSync(dest)) {
                    mkdirSync(dest, { recursive: true });
                }
                
                const items = readdirSync(src);
                
                for (const item of items) {
                    const srcPath = join(src, item);
                    const destPath = join(dest, item);
                    
                    if (statSync(srcPath).isDirectory()) {
                        copyDirectory(srcPath, destPath);
                    } else {
                        copyFileSync(srcPath, destPath);
                    }
                }
            }
            
            if (existsSync(sourceDir)) {
                copyDirectory(sourceDir, targetDir);
                console.log('Images copiées de resources/images vers public/images');
            }
        }
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/css/mobile.css'
            ],
            refresh: true,
        }),
        copyImagesPlugin(),
    ],
});
