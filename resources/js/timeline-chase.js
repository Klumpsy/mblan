/**
 * Scroll-driven chase for the photo timeline: the farmer sprite runs along the
 * timeline rail as you scroll, with Arti chasing right behind. When Arti passes
 * a timeline dot, that dot pulses for a second. Purely decorative and
 * pointer-events-none, so it never interferes with reading the feed.
 *
 * Registered as the Alpine component `timelineChase` (see app.js) on the sprite
 * overlay div *inside* the <ol> timeline rail. Keeping it off the <ol> itself
 * means that if this script ever fails to load, only the decoration breaks --
 * the posts and their (Livewire) edit buttons keep working.
 */
const GAP = 42; // vertical distance between farmer and Arti, in px
const PASS_THRESHOLD = 12; // how close (px) Arti must be to a dot to pulse it
const PULSE_MS = 1000;

export function timelineChase() {
    return {
        farmerY: 0,
        artiY: 0,
        facing: 1, // 1 = moving down, -1 = moving up
        lastScrollY: 0,
        ticking: false,
        rail: null,

        init() {
            this.rail = this.$el.closest('ol');
            this.lastScrollY = window.scrollY;
            const onScroll = () => {
                if (this.ticking) {
                    return;
                }
                this.ticking = true;
                requestAnimationFrame(() => {
                    this.update();
                    this.ticking = false;
                });
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll, { passive: true });
            this.update();
        },

        update() {
            const rail = this.rail;
            if (!rail) {
                return;
            }

            const rect = rail.getBoundingClientRect();
            const railHeight = rail.offsetHeight;
            const viewportMid = window.innerHeight * 0.5;

            // Where the middle of the viewport falls within the rail (0 = top, 1 = bottom).
            const progress = Math.max(0, Math.min(1, (viewportMid - rect.top) / rect.height));
            const targetY = progress * railHeight;

            const delta = window.scrollY - this.lastScrollY;
            if (Math.abs(delta) > 1) {
                this.facing = delta >= 0 ? 1 : -1;
            }
            this.lastScrollY = window.scrollY;

            // Farmer leads in the direction of travel; Arti trails, chasing.
            this.farmerY = targetY + this.facing * (GAP / 2);
            this.artiY = targetY - this.facing * (GAP / 2);

            this.checkDots(rect.top);
        },

        checkDots(railTop) {
            const dots = this.rail.querySelectorAll('[data-timeline-dot]');
            dots.forEach((dot) => {
                const dotRect = dot.getBoundingClientRect();
                const dotY = dotRect.top + dotRect.height / 2 - railTop;

                if (Math.abs(dotY - this.artiY) <= PASS_THRESHOLD) {
                    if (!dot.dataset.pulsing) {
                        dot.dataset.pulsing = '1';
                        dot.classList.add('timeline-dot--pulse');
                        setTimeout(() => {
                            dot.classList.remove('timeline-dot--pulse');
                            delete dot.dataset.pulsing;
                        }, PULSE_MS);
                    }
                }
            });
        },
    };
}
