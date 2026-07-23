import './bootstrap';
import { initEmbers } from './forge/embers';

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * A single shared IntersectionObserver drives every scroll reveal on the page,
 * instead of one observer per element.
 */
const revealObserver = prefersReducedMotion
    ? null
    : new IntersectionObserver(
          (entries) => {
              for (const entry of entries) {
                  if (entry.isIntersecting) {
                      const delay = entry.target.dataset.revealDelay || 0;
                      entry.target.style.transitionDelay = `${delay}ms`;
                      entry.target.classList.add('is-visible');
                      revealObserver.unobserve(entry.target);
                  }
              }
          },
          { threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
      );

/**
 * Register Alpine directives once Alpine (bundled by Livewire) boots.
 */
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // x-reveal: fade/slide a section in when it scrolls into view.
    // Optional modifier for stagger delay: x-reveal.150
    Alpine.directive('reveal', (el, { modifiers }) => {
        el.classList.add('reveal');
        if (!revealObserver) {
            el.classList.add('is-visible');
            return;
        }
        if (modifiers[0]) {
            el.dataset.revealDelay = parseInt(modifiers[0], 10) || 0;
        }
        revealObserver.observe(el);
    });

    // barnGame: walk a character across the farm map to the barn; reaching it
    // opens the login modal. Keyboard (WASD/arrows) + click/tap-to-walk.
    Alpine.data('barnGame', (opts = {}) => ({
        startX: opts.startX ?? 10,
        startY: opts.startY ?? 84,
        px: opts.startX ?? 10,
        py: opts.startY ?? 84,
        tx: null,
        ty: null,
        facing: 1,
        moving: false,
        done: false,
        open: false,
        caught: false,
        keys: {},
        // barn goal centre + trigger radius (all in map %)
        goal: { x: opts.barnX ?? 80, y: opts.barnY ?? 27, r: opts.radius ?? 11 },
        // big rock obstacle you have to walk around
        rock: { x: opts.rockX ?? 49, y: opts.rockY ?? 54, r: opts.rockR ?? 8.5 },
        // Arti the dog: patrols horizontally guarding the approach
        arti: { x: 45, y: 42, dir: 1, min: 40, max: 88, speed: 0.34, r: 6 },

        init() {
            const down = (e) => {
                const k = e.key.toLowerCase();
                this.keys[k] = true;
                if (['arrowup', 'arrowdown', 'arrowleft', 'arrowright', ' '].includes(k)) e.preventDefault();
            };
            const up = (e) => { this.keys[e.key.toLowerCase()] = false; };
            window.addEventListener('keydown', down);
            window.addEventListener('keyup', up);
            const loop = () => { this.step(); requestAnimationFrame(loop); };
            requestAnimationFrame(loop);
        },

        walkTo(e) {
            if (this.done) return;
            const r = this.$refs.map.getBoundingClientRect();
            this.tx = ((e.clientX - r.left) / r.width) * 100;
            this.ty = ((e.clientY - r.top) / r.height) * 100;
        },

        // player would collide with the rock at (nx,ny)?
        blocked(nx, ny) {
            return Math.hypot(nx - this.rock.x, ny - this.rock.y) < this.rock.r;
        },

        resetPlayer() {
            this.caught = true;
            this.px = this.startX; this.py = this.startY;
            this.tx = this.ty = null; this.moving = false;
            setTimeout(() => { this.caught = false; }, 600);
        },

        closeModal() {
            this.open = false;
            // step back out of the barn so you can roam free again
            this.done = false;
            this.py = Math.min(93, this.goal.y + this.goal.r + 7);
            this.tx = this.ty = null;
        },

        step() {
            // Arti patrols regardless
            this.arti.x += this.arti.dir * this.arti.speed;
            if (this.arti.x > this.arti.max) { this.arti.x = this.arti.max; this.arti.dir = -1; }
            if (this.arti.x < this.arti.min) { this.arti.x = this.arti.min; this.arti.dir = 1; }

            if (!this.done) {
                const speed = 0.5;
                let dx = 0, dy = 0;
                const k = this.keys;
                if (k['arrowleft'] || k['a']) dx -= 1;
                if (k['arrowright'] || k['d']) dx += 1;
                if (k['arrowup'] || k['w']) dy -= 1;
                if (k['arrowdown'] || k['s']) dy += 1;

                if (dx || dy) { this.tx = this.ty = null; }
                else if (this.tx !== null) {
                    const ax = this.tx - this.px, ay = this.ty - this.py;
                    const d = Math.hypot(ax, ay);
                    if (d > 0.8) { dx = ax / d; dy = ay / d; }
                    else { this.tx = this.ty = null; }
                }

                if (dx || dy) {
                    const len = Math.hypot(dx, dy) || 1;
                    const vx = (dx / len) * speed, vy = (dy / len) * speed;
                    let nx = Math.min(96, Math.max(3, this.px + vx));
                    let ny = Math.min(93, Math.max(8, this.py + vy));
                    // rock collision with slide (try full, then x-only, then y-only)
                    if (!this.blocked(nx, ny)) { this.px = nx; this.py = ny; }
                    else if (!this.blocked(nx, this.py)) { this.px = nx; }
                    else if (!this.blocked(this.px, ny)) { this.py = ny; }
                    this.moving = true;
                    if (dx < 0) this.facing = -1; else if (dx > 0) this.facing = 1;
                } else {
                    this.moving = false;
                }

                // caught by Arti -> back to start
                if (Math.hypot(this.px - this.arti.x, this.py - this.arti.y) < this.arti.r) {
                    this.resetPlayer();
                }

                // reached the barn
                if (Math.hypot(this.px - this.goal.x, this.py - this.goal.y) < this.goal.r) {
                    this.done = true;
                    this.moving = false;
                    this.px = this.goal.x;
                    this.py = this.goal.y + this.goal.r * 0.55;
                    setTimeout(() => { this.open = true; }, 200);
                }
            }
        },
    }));

    // x-tilt: subtle pointer-follow parallax on cards.
    Alpine.directive('tilt', (el) => {
        if (prefersReducedMotion) return;
        const strength = 8;
        const onMove = (e) => {
            const r = el.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            el.style.transform = `perspective(900px) rotateY(${px * strength}deg) rotateX(${-py * strength}deg) translateZ(0)`;
        };
        const reset = () => {
            el.style.transform = '';
        };
        el.style.transition = 'transform 0.25s ease';
        el.addEventListener('pointermove', onMove);
        el.addEventListener('pointerleave', reset);
    });
});

/**
 * Boot canvas-based hero embers wherever a [data-embers] canvas exists.
 * Re-run after Livewire SPA navigations.
 */
function boot() {
    if (prefersReducedMotion) return;
    document.querySelectorAll('canvas[data-embers]').forEach((c) => initEmbers(c));
}

document.addEventListener('DOMContentLoaded', boot);
document.addEventListener('livewire:navigated', boot);
