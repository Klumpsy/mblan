/**
 * Edition logos are often exported on a solid black square. This lifts the
 * artwork off that square client-side: each pixel's brightest channel
 * becomes its alpha and the colour is un-premultiplied, which is exactly
 * the inverse of compositing over black. Pure black turns transparent, white
 * stays white, cyan becomes cyan with an alpha, so the logo sits in the
 * scene with real transparency instead of relying on blend modes.
 */

// Anything this dark is treated as background, and the ramp above it keeps
// compression noise from leaving a faint ghost of the square.
const FLOOR = 12;

// Pure: mutates an RGBA byte array in place.
export function knockoutDark(data) {
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const max = Math.max(r, g, b);

        if (max <= FLOOR) {
            data[i + 3] = 0;
            continue;
        }

        data[i] = Math.min(255, Math.round((r * 255) / max));
        data[i + 1] = Math.min(255, Math.round((g * 255) / max));
        data[i + 2] = Math.min(255, Math.round((b * 255) / max));

        const alpha = Math.round(((max - FLOOR) / (255 - FLOOR)) * 255);
        data[i + 3] = Math.min(data[i + 3], alpha);
    }

    return data;
}

// Replace an <img>'s pixels with the knocked-out version. Same-origin only
// (canvas would taint otherwise); on any failure the original image stays.
export async function knockoutImage(img) {
    try {
        if (!img.complete) {
            await new Promise((resolve, reject) => {
                img.addEventListener('load', resolve, { once: true });
                img.addEventListener('error', reject, { once: true });
            });
        }
        if (img.decode) await img.decode();

        const w = img.naturalWidth;
        const h = img.naturalHeight;
        if (!w || !h) return;

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(img, 0, 0);

        const imageData = ctx.getImageData(0, 0, w, h);
        knockoutDark(imageData.data);
        ctx.putImageData(imageData, 0, 0);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
        if (!blob) return;

        const url = URL.createObjectURL(blob);
        img.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
        img.src = url;
        img.classList.add('is-knocked-out');
    } catch {
        // Leave the original image in place.
    }
}
