/**
 * Full-screen lightbox for the photo timeline. Clicking a post opens the big
 * image with the poster's name, date and story in a dark gradient at the
 * bottom, and left/right arrows (or swipe on mobile) to move between posts.
 *
 * Reads the posts straight from the DOM ([data-lb] images) at open time, so it
 * always includes any "load more" posts. Registered as the Alpine component
 * `timelineLightbox` (see app.js) on the feed root.
 */
export function timelineLightbox() {
    return {
        open: false,
        index: 0,
        items: [],
        touchX: null,

        openAt(el) {
            const nodes = Array.from(this.$root.querySelectorAll('[data-lb]'));
            this.items = nodes.map((node) => ({
                src: node.dataset.lbSrc,
                name: node.dataset.lbName,
                date: node.dataset.lbDate,
                story: node.dataset.lbStory,
            }));
            this.index = Math.max(0, nodes.indexOf(el));
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';
        },

        next() {
            if (this.items.length) {
                this.index = (this.index + 1) % this.items.length;
            }
        },

        prev() {
            if (this.items.length) {
                this.index = (this.index - 1 + this.items.length) % this.items.length;
            }
        },

        get current() {
            return this.items[this.index] || {};
        },

        onTouchStart(event) {
            this.touchX = event.changedTouches[0].clientX;
        },

        onTouchEnd(event) {
            if (this.touchX === null) {
                return;
            }
            const dx = event.changedTouches[0].clientX - this.touchX;
            if (Math.abs(dx) > 40) {
                dx < 0 ? this.next() : this.prev();
            }
            this.touchX = null;
        },
    };
}
