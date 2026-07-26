/**
 * Client-side image preparation shared by every photo upload form (profile
 * photo, photo timeline). Phone photos are HEIC, which the server rejects by
 * format; browsers other than Safari cannot decode HEIC either. So we convert
 * HEIC to JPEG here (via a WASM decoder loaded only when a HEIC is picked) and
 * downscale every image before it is uploaded. The result: uploads always
 * arrive as a modest JPEG, regardless of device or the server's image support.
 *
 * Registered as the Alpine component `imageUpload` in app.js.
 */

const MAX_DIMENSION = 1600;
const JPEG_QUALITY = 0.82;

function readAsDataUrl(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}

function loadImage(dataUrl) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = dataUrl;
    });
}

function isHeic(file) {
    return /hei[cf]/i.test(file.type) || /\.hei[cf]$/i.test(file.name);
}

async function heicToJpeg(file) {
    const heic2any = (await import('heic2any')).default;
    const result = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.92 });
    const blob = Array.isArray(result) ? result[0] : result;
    return new File([blob], 'foto.jpg', { type: 'image/jpeg' });
}

/**
 * Return a downscaled JPEG File. HEIC is converted first. Non-images and files
 * we genuinely cannot decode are returned unchanged so the server rules apply.
 */
export async function prepareImage(file) {
    if (!file || !file.type || !file.type.startsWith('image/')) {
        return file;
    }

    let source = file;
    if (isHeic(file)) {
        try {
            source = await heicToJpeg(file);
        } catch (e) {
            // Safari can decode HEIC natively; fall through to the canvas path.
        }
    }

    let image;
    try {
        image = await loadImage(await readAsDataUrl(source));
    } catch (e) {
        return source;
    }

    let { width, height } = image;
    if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
        const scale = Math.min(MAX_DIMENSION / width, MAX_DIMENSION / height);
        width = Math.round(width * scale);
        height = Math.round(height * scale);
    }

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(image, 0, 0, width, height);

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY));
    if (!blob) {
        return source;
    }

    const base = (file.name || 'foto').replace(/\.[^.]+$/, '') || 'foto';
    return new File([blob], base + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
}

/**
 * Alpine component backing a photo file input. `property` is the Livewire
 * property the converted file is uploaded to (default `photo`).
 */
export function imageUpload(property = 'photo') {
    return {
        processing: false,
        photoName: null,
        photoPreview: null,

        async handleSelect(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            this.processing = true;
            // Show the site-wide Arti upload overlay for the whole convert+upload.
            window.dispatchEvent(new CustomEvent('mblan-uploading', { detail: { on: true } }));
            const done = () => {
                this.processing = false;
                window.dispatchEvent(new CustomEvent('mblan-uploading', { detail: { on: false } }));
            };

            let upload;
            try {
                upload = await prepareImage(file);
            } catch (e) {
                upload = file;
            }

            this.photoName = upload.name;
            this.photoPreview = URL.createObjectURL(upload);

            this.$wire.upload(property, upload, done, done);
        },
    };
}
