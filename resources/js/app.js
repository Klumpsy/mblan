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
    Alpine.data('barnGame', (maze = {}) => ({
        cols: maze.cols ?? 27,
        rows: maze.rows ?? 17,
        blocked: maze.blocked ?? '',
        startX: maze.start ? maze.start[0] : 6,
        startY: maze.start ? maze.start[1] : 91,
        px: maze.start ? maze.start[0] : 6,
        py: maze.start ? maze.start[1] : 91,
        goal: {
            x: maze.goal ? maze.goal[0] : 94,
            y: maze.goal ? maze.goal[1] : 9,
            r: (100 / (maze.rows ?? 15)) * 1.3,
        },
        safe: maze.safe ?? '',
        gate: maze.gate ?? { c: -1, r: -1 },
        axe: { x: maze.axe ? maze.axe[0] : -1, y: maze.axe ? maze.axe[1] : -1 },
        hasAxe: false,
        chopped: false,
        notice: '',
        arti: {
            x: maze.arti ? maze.arti[0] : 50,
            y: maze.arti ? maze.arti[1] : 50,
            dir: -1, mode: 'patrol', target: null, from: null,
            patrol: 0.30, chase: 0.44, r: 2.6, detect: 22, distract: 0,
            ability: null, abilityT: 0, cooldown: 480, scale: 1, ghost: false, teleFx: 0,
        },
        bones: [],
        planted: [],
        barnMoved: false, wizard: false, wizardT: 0, homeGoal: null,
        tx: null, ty: null, facing: 1, moving: false,
        done: false, open: false, caught: false,
        caughtCount: 0,
        startedAt: null, timeMs: 0, clock: '0:00', _lastSec: -1,
        lastSafe: null,
        keys: {},

        cookie(name) {
            const m = document.cookie.match('(?:^|; )' + name + '=([^;]*)');
            return m ? decodeURIComponent(m[1]) : null;
        },
        setCookie(name, val) {
            document.cookie = name + '=' + encodeURIComponent(val) + '; path=/; max-age=31536000; samesite=lax';
        },

        init() {
            this.caughtCount = parseInt(this.cookie('mblan_caught') || '0', 10) || 0;
            this.homeGoal = { x: this.goal.x, y: this.goal.y, r: this.goal.r };
            this.lastSafe = { x: this.px, y: this.py };
            this.spawnBone(); this.spawnBone();
            const down = (e) => {
                const k = e.key.toLowerCase();
                this.keys[k] = true;
                if (['arrowup', 'arrowdown', 'arrowleft', 'arrowright', ' '].includes(k)) e.preventDefault();
            };
            const up = (e) => { this.keys[e.key.toLowerCase()] = false; };
            window.addEventListener('keydown', down);
            window.addEventListener('keyup', up);
            // The loop must never die: a stray error in one frame should not freeze the game.
            const loop = () => {
                try { this.step(); } catch (e) { /* keep the game alive */ console.error(e); }
                requestAnimationFrame(loop);
            };
            requestAnimationFrame(loop);
        },

        // --- grid helpers ---
        cellC(x) { return Math.floor((x / 100) * this.cols); },
        cellR(y) { return Math.floor((y / 100) * this.rows); },
        centerX(c) { return ((c + 0.5) / this.cols) * 100; },
        centerY(r) { return ((r + 0.5) / this.rows) * 100; },
        isWallCell(c, r) {
            if (c < 0 || r < 0 || c >= this.cols || r >= this.rows) return true;
            if (this.chopped && c === this.gate.c && r === this.gate.r) return false; // felled tree
            return this.blocked[r * this.cols + c] === '1';
        },
        isWall(x, y) { return this.isWallCell(this.cellC(x), this.cellR(y)); },
        passable(c, r) { return !this.isWallCell(c, r); },
        isSafeCell(c, r) { return this.safe && this.safe[r * this.cols + c] === '1'; },
        // cells Arti may use: never the gate, never the safe channel
        artiPassable(c, r) {
            if (c === this.gate.c && r === this.gate.r) return false;
            if (this.safe && this.safe[r * this.cols + c] === '1') return false;
            return !this.isWallCell(c, r);
        },

        walkTo(e) {
            if (this.done) return;
            const r = this.$refs.map.getBoundingClientRect();
            this.tx = ((e.clientX - r.left) / r.width) * 100;
            this.ty = ((e.clientY - r.top) / r.height) * 100;
        },

        resetPlayer() {
            this.caughtCount += 1;              // keep the total attempts...
            this.setCookie('mblan_caught', this.caughtCount);
            // ...and keep the running timer (startedAt), but reset the whole puzzle:
            this.caught = true;
            this.px = this.startX; this.py = this.startY;
            this.tx = this.ty = null; this.moving = false;
            this.hasAxe = false; this.chopped = false;   // axe + gate tree back
            this.planted = [];                            // clear planted trees
            this.barnMoved = false; this.wizard = false; this.wizardT = 0;
            if (this.homeGoal) this.goal = { ...this.homeGoal };  // barn back to the top-right
            // reset Arti's state/abilities
            this.arti.ability = null; this.arti.abilityT = 0; this.arti.ghost = false;
            this.arti.scale = 1; this.arti.distract = 0; this.arti.target = null;
            this.arti.cooldown = 480;
            this.lastSafe = { x: this.px, y: this.py };
            clearTimeout(this._caughtT);
            this._caughtT = setTimeout(() => { this.caught = false; }, 1800);
        },

        // on-screen d-pad (mobile): set/clear a movement key
        press(k, on) { this.keys[k] = on; },

        closeModal() {
            this.open = false;
            this.done = false;
            if (this.lastSafe) { this.px = this.lastSafe.x; this.py = this.lastSafe.y; }
            this.tx = this.ty = null;
        },

        // BFS one step from Arti's cell toward the player's cell (shortest path)
        chaseStep(sc, sr, tc, tr) {
            const key = (c, r) => r * this.cols + c;
            const q = [[sc, sr]];
            const prev = new Map(); prev.set(key(sc, sr), null);
            let found = false;
            while (q.length) {
                const [c, r] = q.shift();
                if (c === tc && r === tr) { found = true; break; }
                for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
                    const nc = c + dc, nr = r + dr;
                    if (this.artiPassable(nc, nr) && !prev.has(key(nc, nr))) {
                        prev.set(key(nc, nr), [c, r]);
                        q.push([nc, nr]);
                    }
                }
            }
            if (!found) return null;
            let cur = [tc, tr], p = prev.get(key(tc, tr));
            while (p && !(p[0] === sc && p[1] === sr)) { cur = p; p = prev.get(key(cur[0], cur[1])); }
            return cur;
        },

        moveArti() {
            const a = this.arti;

            if (a.teleFx > 0) a.teleFx -= 1;
            if (this.wizardT > 0) { this.wizardT -= 1; if (this.wizardT === 0) this.wizard = false; }

            // ability lifecycle (only while the game is live)
            if (!this.done) {
                if (a.ability) {
                    a.abilityT -= 1;
                    if (a.ability === 'farmer' && a.abilityT % 55 === 0) this.plantTree();
                    if (a.abilityT <= 0) this.endAbility();
                } else if (a.distract === 0) {
                    a.cooldown -= 1;
                    if (a.cooldown <= 0) this.triggerAbility();
                }
            }

            // busy eating a bone -> stands still, not chasing
            if (a.distract > 0) { a.distract -= 1; a.mode = 'patrol'; return; }

            // GHOST: glide straight through walls toward the player
            if (a.ghost) {
                const spd = a.chase;
                const dx = this.px - a.x, dy = this.py - a.y, d = Math.hypot(dx, dy) || 1;
                a.x += (dx / d) * spd; a.y += (dy / d) * spd;
                if (Math.abs(dx) > 0.1) a.dir = dx < 0 ? -1 : 1;
                return;
            }

            // reached a bone? get distracted and respawn a fresh one elsewhere
            for (let i = 0; i < this.bones.length; i++) {
                if (Math.hypot(a.x - this.bones[i].x, a.y - this.bones[i].y) < 2.8) {
                    this.bones.splice(i, 1);
                    a.distract = 200;           // ~3.3s at 60fps
                    a.target = null;
                    this.spawnBone();
                    this.flash('Arti is afgeleid door een bot!');
                    return;
                }
            }

            const ac = this.cellC(a.x), ar = this.cellR(a.y);
            const pc = this.cellC(this.px), pr = this.cellR(this.py);
            const distToPlayer = Math.hypot(this.px - a.x, this.py - a.y);
            a.mode = distToPlayer < a.detect ? 'chase' : (distToPlayer > a.detect * 1.7 ? 'patrol' : a.mode);

            // pick a new target cell when Arti has reached its current one
            const atTarget = !a.target ||
                (Math.abs(a.x - this.centerX(a.target[0])) < 0.6 && Math.abs(a.y - this.centerY(a.target[1])) < 0.6);
            if (atTarget) {
                if (a.target) { a.from = [ac, ar]; a.x = this.centerX(a.target[0]); a.y = this.centerY(a.target[1]); }
                let next = null;
                if (a.mode === 'chase') next = this.chaseStep(ac, ar, pc, pr);
                if (!next) {
                    // patrol: random passable neighbour, avoid reversing unless dead-end
                    const opts = [];
                    for (const [dc, dr] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
                        const nc = ac + dc, nr = ar + dr;
                        if (this.artiPassable(nc, nr) && !(a.from && a.from[0] === nc && a.from[1] === nr)) opts.push([nc, nr]);
                    }
                    if (opts.length) next = opts[Math.floor(this.rnd() * opts.length)];
                    else if (a.from) next = a.from;
                }
                a.target = next;
            }

            if (a.target) {
                const mul = a.ability === 'speed' ? 1.9 : 1;
                const spd = (a.mode === 'chase' ? a.chase : a.patrol) * mul;
                const cx = this.centerX(a.target[0]), cy = this.centerY(a.target[1]);
                const dx = cx - a.x, dy = cy - a.y, d = Math.hypot(dx, dy) || 1;
                a.x += (dx / d) * Math.min(spd, d);
                a.y += (dy / d) * Math.min(spd, d);
                if (Math.abs(dx) > 0.1) a.dir = dx < 0 ? -1 : 1;
            }
        },

        // deterministic-ish rng without Math.random reliance issues
        rnd() { this._s = (this._s || 7) * 16807 % 2147483647; return this._s / 2147483647; },

        flash(msg) {
            this.notice = msg;
            clearTimeout(this._noticeT);
            this._noticeT = setTimeout(() => { this.notice = ''; }, 2600);
        },

        // drop a bone on a random road cell (never the safe channel, gate, edges, or near player/barn)
        spawnBone() {
            for (let i = 0; i < 200; i++) {
                const c = 1 + Math.floor(Math.random() * (this.cols - 2));
                const r = 1 + Math.floor(Math.random() * (this.rows - 2));
                if (!this.artiPassable(c, r)) continue;
                const x = this.centerX(c), y = this.centerY(r);
                if (Math.hypot(x - this.px, y - this.py) < 14) continue;
                if (Math.hypot(x - this.goal.x, y - this.goal.y) < 14) continue;
                this.bones.push({ x, y });
                return;
            }
        },

        isPlanted(c, r) { return this.planted.some(p => p.c === c && p.r === r); },

        playerBlocked(x, y) {
            if (this.isWall(x, y)) return true;
            // planted trees block you unless you carry the axe
            if (!this.hasAxe && this.isPlanted(this.cellC(x), this.cellR(y))) return true;
            return false;
        },

        formatTime(ms) {
            const s = Math.floor(ms / 1000);
            return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
        },

        // --- Arti's random funny abilities ---
        triggerAbility() {
            const a = this.arti;
            const pool = ['teleport', 'giant', 'ghost', 'speed', 'farmer'];
            const choice = pool[Math.floor(Math.random() * pool.length)];
            a.ability = choice;
            const msg = {
                teleport: 'Arti ging in teleport-modus!',
                giant: 'Arti werd GIGANTISCH!',
                ghost: 'Arti ging in spook-modus!',
                speed: 'Arti ging supersnel!',
                farmer: 'Arti ging in boer-modus!',
            };
            this.flash(msg[choice]);

            if (choice === 'teleport') { this.teleportArti(); a.abilityT = 45; }
            else if (choice === 'giant') { a.scale = 2.1; a.abilityT = 320; }
            else if (choice === 'ghost') { a.ghost = true; a.abilityT = 260; }
            else if (choice === 'speed') { a.abilityT = 320; }
            else if (choice === 'farmer') { a.abilityT = 380; }
        },

        endAbility() {
            const a = this.arti;
            a.ability = null; a.abilityT = 0; a.ghost = false; a.scale = 1;
            a.cooldown = 360 + Math.floor(Math.random() * 360); // 6-12s till next
            // a ghost pass can leave Arti inside a wall; snap back onto a road
            if (!this.artiPassable(this.cellC(a.x), this.cellR(a.y))) {
                const snap = this.nearestPassable(this.cellC(a.x), this.cellR(a.y));
                if (snap) { a.x = this.centerX(snap[0]); a.y = this.centerY(snap[1]); }
                a.target = null; a.from = null;
            }
        },

        teleportArti() {
            const a = this.arti;
            for (let i = 0; i < 200; i++) {
                const c = 1 + Math.floor(Math.random() * (this.cols - 2));
                const r = 1 + Math.floor(Math.random() * (this.rows - 2));
                if (!this.artiPassable(c, r)) continue;
                a.x = this.centerX(c); a.y = this.centerY(r);
                a.target = null; a.from = null; a.teleFx = 26;
                return;
            }
        },

        // one-time prank: the first time you almost reach the barn, wizard-Arti
        // teleports the barn to where the axe used to be.
        wizardPrank() {
            this.barnMoved = true;
            this.wizard = true;
            this.wizardT = 260;
            this.arti.teleFx = 30;
            // reassign the whole object so Alpine re-renders the barn + win math together
            this.goal = { x: this.axe.x, y: this.axe.y, r: this.goal.r };
            this.flash('Arti ging in TOVENAAR-modus en verplaatste de schuur!');
        },

        // nearest road cell to (c,r), searching outward
        nearestPassable(c, r) {
            for (let rad = 0; rad < Math.max(this.cols, this.rows); rad++) {
                for (let dr = -rad; dr <= rad; dr++) {
                    for (let dc = -rad; dc <= rad; dc++) {
                        if (Math.abs(dc) !== rad && Math.abs(dr) !== rad) continue;
                        if (this.artiPassable(c + dc, r + dr)) return [c + dc, r + dr];
                    }
                }
            }
            return null;
        },

        plantTree() {
            const a = this.arti;
            const ac = this.cellC(a.x), ar = this.cellR(a.y);
            const cands = [[ac, ar], [ac + 1, ar], [ac - 1, ar], [ac, ar + 1], [ac, ar - 1]].sort(() => Math.random() - 0.5);
            for (const [c, r] of cands) {
                if (!this.artiPassable(c, r)) continue;
                if (c === this.gate.c && r === this.gate.r) continue;
                if (this.isPlanted(c, r)) continue;
                if (c === this.cellC(this.px) && r === this.cellR(this.py)) continue; // never trap the player's cell
                if (c === this.cellC(this.goal.x) && r === this.cellR(this.goal.y)) continue; // never block the barn
                if (this.planted.length > 24) return;
                this.planted.push({ c, r });
                return;
            }
        },

        step() {
            this.moveArti();

            if (this.done) return;

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
                if (d > 0.8) { dx = ax / d; dy = ay / d; } else { this.tx = this.ty = null; }
            }

            if (dx || dy) {
                if (this.startedAt === null) this.startedAt = Date.now();
                const len = Math.hypot(dx, dy) || 1;
                const vx = (dx / len) * speed, vy = (dy / len) * speed;
                const nx = Math.min(98, Math.max(2, this.px + vx));
                const ny = Math.min(98, Math.max(2, this.py + vy));
                if (!this.playerBlocked(nx, ny)) { this.px = nx; this.py = ny; }
                else if (!this.playerBlocked(nx, this.py)) { this.px = nx; }
                else if (!this.playerBlocked(this.px, ny)) { this.py = ny; }
                // chop a planted tree you step onto while carrying the axe
                if (this.hasAxe && this.planted.length) {
                    const idx = this.planted.findIndex(p => p.c === this.cellC(this.px) && p.r === this.cellR(this.py));
                    if (idx !== -1) this.planted.splice(idx, 1);
                }
                this.moving = true;
                if (dx < 0) this.facing = -1; else if (dx > 0) this.facing = 1;
            } else {
                this.moving = false;
            }

            // live timer
            if (this.startedAt !== null) {
                const el = Date.now() - this.startedAt;
                const s = Math.floor(el / 1000);
                if (s !== this._lastSec) { this._lastSec = s; this.clock = this.formatTime(el); }
            }

            const cellW = 100 / this.cols;
            // pick up the hidden axe
            if (!this.hasAxe && this.axe.x >= 0 &&
                Math.hypot(this.px - this.axe.x, this.py - this.axe.y) < cellW * 0.9) {
                this.hasAxe = true;
                this.flash('Bijl gevonden! Hak nu de boom om.');
            }
            // chop the gate tree once you have the axe
            if (this.hasAxe && !this.chopped && this.gate.c >= 0) {
                const gx = this.centerX(this.gate.c), gy = this.centerY(this.gate.r);
                if (Math.hypot(this.px - gx, this.py - gy) < cellW * 1.1) {
                    this.chopped = true;
                    this.flash('Boom omgehakt! Het veilige pad is open.');
                }
            }

            // remember a safe spot away from the barn to return to on cancel
            if (Math.hypot(this.px - this.goal.x, this.py - this.goal.y) > this.goal.r + 4) {
                this.lastSafe = { x: this.px, y: this.py };
            }

            const catchR = this.arti.r * (this.arti.ability === 'giant' ? 2.2 : 1);
            if (this.arti.distract === 0 && Math.hypot(this.px - this.arti.x, this.py - this.arti.y) < catchR) {
                this.resetPlayer();
            }

            // wizard prank: almost at the barn (in the safe channel) for the first time
            if (!this.barnMoved &&
                this.isSafeCell(this.cellC(this.px), this.cellR(this.py)) &&
                Math.hypot(this.px - this.goal.x, this.py - this.goal.y) < this.goal.r * 2.1) {
                this.wizardPrank();
            }

            // reach the barn: from the safe channel, or anywhere once the wizard moved it
            if ((this.barnMoved || this.isSafeCell(this.cellC(this.px), this.cellR(this.py))) &&
                Math.hypot(this.px - this.goal.x, this.py - this.goal.y) < this.goal.r) {
                this.done = true;
                this.moving = false;
                this.timeMs = this.startedAt ? Date.now() - this.startedAt : 0;
                this.setCookie('mblan_done', '1');
                if (this.timeMs > 0) this.setCookie('mblan_time', this.timeMs);
                setTimeout(() => { this.open = true; }, 200);
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
