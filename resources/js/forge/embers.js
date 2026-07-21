/**
 * Lightweight forge-ember particle field for the hero.
 * Reads the active accent colour from the CSS variable --c-primary-400 so it
 * always matches the current edition theme. rAF-driven, capped particle count,
 * auto-pauses when scrolled out of view.
 */
export function initEmbers(canvas) {
    if (canvas.dataset.embersReady === '1') return;
    canvas.dataset.embersReady = '1';

    const ctx = canvas.getContext('2d');
    const DPR = Math.min(window.devicePixelRatio || 1, 2);
    let width = 0;
    let height = 0;
    let particles = [];
    let running = true;
    let rafId = null;

    const accent = () => {
        const v = getComputedStyle(document.documentElement)
            .getPropertyValue('--c-primary-400')
            .trim();
        return v || '150 220 154';
    };

    function resize() {
        const rect = canvas.getBoundingClientRect();
        width = rect.width;
        height = rect.height;
        canvas.width = width * DPR;
        canvas.height = height * DPR;
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);

        const target = Math.min(180, Math.round((width * height) / 9000));
        particles = Array.from({ length: target }, () => spawn(true));
    }

    function spawn(initial) {
        return {
            x: Math.random() * width,
            y: initial ? Math.random() * height : height + 10,
            r: 0.6 + Math.random() * 2.2,
            vy: 0.2 + Math.random() * 0.9,
            vx: (Math.random() - 0.5) * 0.4,
            life: 0,
            maxLife: 180 + Math.random() * 220,
            flicker: 0.4 + Math.random() * 0.6,
        };
    }

    function frame() {
        if (!running) return;
        ctx.clearRect(0, 0, width, height);
        const rgb = accent();

        for (const p of particles) {
            p.life += 1;
            p.y -= p.vy;
            p.x += p.vx + Math.sin(p.life / 30) * 0.2;

            if (p.y < -10 || p.life > p.maxLife) {
                Object.assign(p, spawn(false));
                continue;
            }

            const fade = 1 - p.life / p.maxLife;
            const alpha = fade * p.flicker;
            ctx.beginPath();
            ctx.fillStyle = `rgb(${rgb} / ${alpha.toFixed(3)})`;
            ctx.shadowBlur = 8;
            ctx.shadowColor = `rgb(${rgb} / ${(alpha * 0.8).toFixed(3)})`;
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.shadowBlur = 0;
        rafId = requestAnimationFrame(frame);
    }

    const visObserver = new IntersectionObserver(
        ([entry]) => {
            running = entry.isIntersecting;
            if (running && rafId === null) frame();
            if (!running && rafId !== null) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        },
        { threshold: 0 }
    );

    window.addEventListener('resize', resize, { passive: true });
    resize();
    visObserver.observe(canvas);
    frame();
}
