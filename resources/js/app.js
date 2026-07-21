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
