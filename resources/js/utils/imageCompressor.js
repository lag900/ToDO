/**
 * Compress images before upload using browser-based canvas.
 * Converts to WebP when possible and reduces quality.
 */
export async function compressImage(file, { maxWidth = 1200, maxHeight = 1200, quality = 0.7 } = {}) {
    if (!file.type.startsWith('image/')) return file;

    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                // Respect max dimensions
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Try to convert to WebP, fallback to JPEG
                const type = 'image/webp';
                canvas.toBlob((blob) => {
                    if (!blob) {
                        reject(new Error('Canvas toBlob failed'));
                        return;
                    }
                    // Create a new file object from the blob
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".webp", {
                        type: type,
                        lastModified: Date.now()
                    });
                    
                    // If the compressed version is actually larger (rare for webp/jpeg), return original
                    if (compressedFile.size > file.size) {
                      resolve(file);
                    } else {
                      resolve(compressedFile);
                    }
                }, type, quality);
            };
            img.onerror = reject;
        };
        reader.onerror = reject;
    });
}
