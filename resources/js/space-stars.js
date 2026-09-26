/**
 * Canvas starfield for the landing showcase.
 *
 * A few hundred stars, each with its own twinkle phase and depth, drift very
 * slowly across the backdrop; now and then one shoots across with a fading
 * trail. Everything is scaled for the device pixel ratio so the tiny stars
 * stay crisp on retina screens. Under reduced motion a single static frame
 * is drawn instead of a loop.
 */

// Pure: scatter `count` stars over a `width` x `height` box. `random` is
// injectable so the layout is testable.
export function makeStars(count, width, height, random = Math.random) {
    const stars = [];

    for (let i = 0; i < count; i++) {
        const depth = 0.25 + random() * 0.75; // 1 = nearest, brightest, biggest
        const accent = random() < 0.18;

        stars.push({
            x: random() * width,
            y: random() * height,
            r: depth < 0.6 ? 0.6 + depth * 0.6 : 0.9 + (depth - 0.6) * 2.2,
            depth,
            phase: random() * Math.PI * 2,
            speed: 0.6 + random() * 1.6,
            accent,
        });
    }

    return stars;
}

// Pure: a smooth brightness between `min` and `max` for time `t`.
export function twinkle(t, speed, min, max) {
    return min + (max - min) * (0.5 + 0.5 * Math.sin(t * speed));
}

function accentColour() {
    const raw = getComputedStyle(document.documentElement).getPropertyValue('--c-primary-300').trim();

    return raw ? raw.split(/\s+/).join(',') : '140,228,234';
}

export function initSpaceStars(canvas, { reducedMotion = false } = {}) {
    const ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) return;

    let width = 0;
    let height = 0;
    let dpr = 1;
    let stars = [];
    let shooters = [];
    let nextShooter = 0;
    let raf = null;
    let last = 0;
    let elapsed = 0;
    const accent = accentColour();

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = Math.max(1, Math.round(rect.width));
        height = Math.max(1, Math.round(rect.height));
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.min(420, Math.round((width * height) / 5200));
        stars = makeStars(count, width, height);
        draw(0);
    };

    const spawnShooter = () => {
        const fromRight = Math.random() < 0.65;
        const speed = 520 + Math.random() * 260;
        const angle = fromRight ? Math.PI * 0.82 : Math.PI * 0.18; // down-left or down-right

        shooters.push({
            x: fromRight ? width * (0.45 + Math.random() * 0.55) : width * Math.random() * 0.5,
            y: height * Math.random() * 0.35,
            vx: Math.cos(angle) * speed,
            vy: Math.sin(angle) * speed,
            life: 0,
            maxLife: 0.7 + Math.random() * 0.5,
        });
    };

    const draw = (dt) => {
        elapsed += dt;
        ctx.clearRect(0, 0, width, height);

        for (const s of stars) {
            if (dt) {
                s.x += (0.35 + s.depth) * 1.6 * dt;
                if (s.x > width + 2) s.x -= width + 4;
            }

            const a = twinkle(elapsed + s.phase, s.speed, 0.22, 1) * (0.35 + s.depth * 0.65);
            ctx.fillStyle = s.accent ? `rgba(${accent},${a.toFixed(3)})` : `rgba(236,246,255,${a.toFixed(3)})`;

            if (s.r <= 1) {
                // Sub-pixel rects keep the tiny stars crisp instead of smeared arcs.
                ctx.fillRect(s.x, s.y, s.r + 0.4, s.r + 0.4);
            } else {
                ctx.beginPath();
                ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        if (!dt) return;

        nextShooter -= dt;
        if (nextShooter <= 0) {
            spawnShooter();
            nextShooter = 3.5 + Math.random() * 5;
        }

        ctx.lineCap = 'round';
        shooters = shooters.filter((sh) => {
            sh.life += dt;
            sh.x += sh.vx * dt;
            sh.y += sh.vy * dt;

            const p = sh.life / sh.maxLife;
            if (p >= 1) return false;

            const fade = p < 0.2 ? p / 0.2 : 1 - (p - 0.2) / 0.8;
            const tail = 0.16;
            const tx = sh.x - sh.vx * tail;
            const ty = sh.y - sh.vy * tail;
            const grad = ctx.createLinearGradient(tx, ty, sh.x, sh.y);
            grad.addColorStop(0, 'rgba(236,246,255,0)');
            grad.addColorStop(1, `rgba(236,246,255,${(0.9 * fade).toFixed(3)})`);

            ctx.strokeStyle = grad;
            ctx.lineWidth = 1.6;
            ctx.beginPath();
            ctx.moveTo(tx, ty);
            ctx.lineTo(sh.x, sh.y);
            ctx.stroke();

            ctx.fillStyle = `rgba(${accent},${(0.9 * fade).toFixed(3)})`;
            ctx.beginPath();
            ctx.arc(sh.x, sh.y, 1.6, 0, Math.PI * 2);
            ctx.fill();

            return true;
        });
    };

    const frame = (now) => {
        const dt = last ? Math.min((now - last) / 1000, 0.05) : 0;
        last = now;
        draw(dt);
        raf = requestAnimationFrame(frame);
    };

    const start = () => {
        if (raf || reducedMotion) return;
        last = 0;
        raf = requestAnimationFrame(frame);
    };

    const stop = () => {
        if (raf) cancelAnimationFrame(raf);
        raf = null;
    };

    resize();
    nextShooter = 1.5;

    if ('ResizeObserver' in window) {
        new ResizeObserver(resize).observe(canvas);
    } else {
        window.addEventListener('resize', resize);
    }

    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
    start();
}

/**
 * Pointer parallax: every `[data-parallax="depth"]` layer eases toward an
 * offset that follows the cursor, deeper layers moving more. The offset is
 * written to CSS variables (--px/--py) so it never fights the layers' own
 * keyframe animations. Skipped on touch screens and under reduced motion.
 */
export function initParallax(root = document, { strength = 30 } = {}) {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    const layers = [...root.querySelectorAll('[data-parallax]')].map((el) => ({
        el,
        depth: parseFloat(el.dataset.parallax) || 0,
    }));
    if (!layers.length) return;

    let targetX = 0;
    let targetY = 0;
    let x = 0;
    let y = 0;
    let raf = null;

    const step = () => {
        x += (targetX - x) * 0.055;
        y += (targetY - y) * 0.055;

        for (const layer of layers) {
            layer.el.style.setProperty('--px', `${(-x * layer.depth * strength).toFixed(2)}px`);
            layer.el.style.setProperty('--py', `${(-y * layer.depth * strength).toFixed(2)}px`);
        }

        if (Math.abs(targetX - x) > 0.0015 || Math.abs(targetY - y) > 0.0015) {
            raf = requestAnimationFrame(step);
        } else {
            raf = null;
        }
    };

    const nudge = () => {
        if (!raf) raf = requestAnimationFrame(step);
    };

    window.addEventListener(
        'pointermove',
        (e) => {
            targetX = (e.clientX / window.innerWidth - 0.5) * 2;
            targetY = (e.clientY / window.innerHeight - 0.5) * 2;
            nudge();
        },
        { passive: true },
    );

    document.addEventListener('pointerleave', () => {
        targetX = 0;
        targetY = 0;
        nudge();
    });
}
