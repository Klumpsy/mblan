/**
 * De editie-klassieker: "Arti in Space" — een Galaxian-achtige, oneindige
 * shooter. Arti en de boer delen een schip; golven aliens komen in formatie,
 * duiken aan en schieten terug. Hoe hoger je score, hoe sneller en feller
 * alles wordt. Bestuur met slepen/muis of pijltjes (A/D); schieten gaat
 * vanzelf. Game over zet de mblan_score cookie; na inloggen synct de app-
 * layout die naar /game/sync (ingelogde spelers syncen direct).
 */
export default (config = {}) => ({
    // logical playfield; the canvas is scaled to fit the viewport
    W: 320,
    H: 480,

    score: 0,
    hiscore: 0,
    wave: 0,
    lives: 3,
    running: false,
    over: false,
    open: false,

    // internal state (not reactive-critical, but Alpine proxying is fine here)
    _ctx: null,
    _raf: null,
    _last: 0,
    _sprites: {},
    _stars: [],
    _player: null,
    _bullets: [],
    _enemyBullets: [],
    _enemies: [],
    _particles: [],
    _ufo: null,
    _fireAt: 0,
    _ufoAt: 0,
    _diveAt: 0,
    _keys: {},
    _targetX: null,
    _formation: { dir: 1, x: 0, y: 0, speed: 22 },

    cookie(name) {
        const m = document.cookie.match('(?:^|; )' + name + '=([^;]*)');
        return m ? decodeURIComponent(m[1]) : null;
    },
    setCookie(name, val) {
        document.cookie = name + '=' + encodeURIComponent(val) + '; path=/; max-age=31536000; samesite=lax';
    },
    eraseCookie(name) {
        document.cookie = name + '=; path=/; max-age=0; samesite=lax';
    },

    init() {
        this.hiscore = parseInt(localStorage.getItem('mblan_hiscore') || '0', 10) || 0;

        // A fresh visit is a fresh attempt; cookies are only the one-way
        // handoff of the final score to the sync on the next logged-in page.
        this.eraseCookie('mblan_score');
        this.eraseCookie('mblan_done');

        const canvas = this.$refs.canvas;
        canvas.width = this.W;
        canvas.height = this.H;
        this._ctx = canvas.getContext('2d');
        this._ctx.imageSmoothingEnabled = false;

        for (const [key, url] of Object.entries(config.sprites || {})) {
            const img = new Image();
            img.src = url;
            this._sprites[key] = img;
        }

        for (let i = 0; i < 70; i++) {
            this._stars.push({
                x: Math.random() * this.W,
                y: Math.random() * this.H,
                s: 0.4 + Math.random() * 1.6,
                v: 12 + Math.random() * 40,
            });
        }

        window.addEventListener('keydown', (e) => {
            const k = e.key.toLowerCase();
            this._keys[k] = true;
            if (['arrowleft', 'arrowright', ' '].includes(k)) e.preventDefault();
            if (k === ' ' && this.over) this.restart();
        });
        window.addEventListener('keyup', (e) => { this._keys[e.key.toLowerCase()] = false; });

        const stage = this.$refs.stage;
        const toGameX = (clientX) => {
            const r = stage.getBoundingClientRect();
            return ((clientX - r.left) / r.width) * this.W;
        };
        stage.addEventListener('pointerdown', (e) => { this._targetX = toGameX(e.clientX); });
        stage.addEventListener('pointermove', (e) => {
            if (e.pointerType === 'mouse' || e.buttons > 0 || e.pointerType === 'touch') {
                this._targetX = toGameX(e.clientX);
            }
        });

        this.restart();

        const loop = (t) => {
            try { this.step(t); } catch (e) { console.error(e); }
            this._raf = requestAnimationFrame(loop);
        };
        this._raf = requestAnimationFrame(loop);

        // handle for browser-based verification
        window.__spaceClassic = this;
    },

    restart() {
        this.score = 0;
        this.wave = 0;
        this.lives = 3;
        this.over = false;
        this.open = false;
        this.running = true;
        this._bullets = [];
        this._enemyBullets = [];
        this._particles = [];
        this._ufo = null;
        this._targetX = null;
        this._player = { x: this.W / 2, y: this.H - 34, w: 24, h: 14, invuln: 2, dead: false };
        this.eraseCookie('mblan_score');
        this.eraseCookie('mblan_done');
        this.spawnWave();
    },

    /** Difficulty multiplier: grows with score, plus a pinch per wave. Infinite. */
    difficulty() {
        return 1 + Math.min(3, this.score / 1200) + this.wave * 0.06;
    },

    spawnWave() {
        this.wave++;
        const cols = 8;
        const rows = Math.min(5, 3 + Math.floor(this.wave / 3));
        const gapX = 30;
        const gapY = 24;
        const startX = (this.W - (cols - 1) * gapX) / 2;

        this._enemies = [];
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                this._enemies.push({
                    slotX: startX + c * gapX,
                    slotY: 60 + r * gapY,
                    x: startX + c * gapX,
                    y: -30 - r * 20,
                    w: 18, h: 12,
                    type: r < 2 ? 'b' : 'a',
                    mode: 'enter', // enter -> formation -> dive
                    t: Math.random() * Math.PI * 2,
                    diveX: 0,
                });
            }
        }
        this._formation = { dir: 1, x: 0, y: 0, speed: 20 + 8 * this.difficulty() };
        this._diveAt = 2.5;
        this._ufoAt = 8 + Math.random() * 10;
    },

    step(t) {
        const dt = Math.min(0.033, (t - this._last) / 1000 || 0.016);
        this._last = t;

        if (this.running && !this.over) this.update(dt);
        this.draw();
    },

    update(dt) {
        const d = this.difficulty();
        const p = this._player;

        // --- player movement: keys or pointer target
        const speed = 190;
        if (this._keys['arrowleft'] || this._keys['a']) { p.x -= speed * dt; this._targetX = null; }
        if (this._keys['arrowright'] || this._keys['d']) { p.x += speed * dt; this._targetX = null; }
        if (this._targetX !== null) {
            const dx = this._targetX - p.x;
            p.x += Math.max(-speed * 1.4 * dt, Math.min(speed * 1.4 * dt, dx));
        }
        p.x = Math.max(p.w / 2, Math.min(this.W - p.w / 2, p.x));
        if (p.invuln > 0) p.invuln -= dt;

        // --- autofire
        this._fireAt -= dt;
        if (this._fireAt <= 0 && !p.dead) {
            this._bullets.push({ x: p.x, y: p.y - 10, v: 300 });
            this._fireAt = Math.max(0.16, 0.3 - d * 0.03);
        }

        // --- bullets
        this._bullets = this._bullets.filter((b) => (b.y -= b.v * dt) > -10);
        this._enemyBullets = this._enemyBullets.filter((b) => (b.y += b.v * dt) < this.H + 10);

        // --- formation sway + slow descent
        const f = this._formation;
        f.x += f.dir * f.speed * d * 0.55 * dt;
        if (f.x > 18 || f.x < -18) {
            f.dir *= -1;
            f.y += 6;
        }
        f.y += dt * (1.2 + d * 0.9);

        // --- enemies
        this._diveAt -= dt;
        const formationAlive = this._enemies.filter((e) => e.mode === 'formation');
        if (this._diveAt <= 0 && formationAlive.length) {
            const diver = formationAlive[Math.floor(Math.random() * formationAlive.length)];
            diver.mode = 'dive';
            diver.t = 0;
            diver.diveX = diver.x;
            this._diveAt = Math.max(0.5, 2.4 - d * 0.45);
        }

        for (const e of this._enemies) {
            e.anim = (e.anim || 0) + dt;
            if (e.mode === 'enter') {
                e.y += 90 * dt;
                e.x = e.slotX + f.x;
                if (e.y >= e.slotY + f.y) e.mode = 'formation';
            } else if (e.mode === 'formation') {
                e.x = e.slotX + f.x;
                e.y = e.slotY + f.y;
                // formation shooters
                if (Math.random() < dt * 0.06 * d) {
                    this._enemyBullets.push({ x: e.x, y: e.y + 8, v: 90 + 45 * d });
                }
            } else if (e.mode === 'dive') {
                e.t += dt;
                e.y += (120 + 40 * d) * dt;
                e.x = e.diveX + Math.sin(e.t * 3.2) * 46;
                if (Math.random() < dt * 0.5) {
                    this._enemyBullets.push({ x: e.x, y: e.y + 8, v: 110 + 50 * d });
                }
                if (e.y > this.H + 20) {
                    e.mode = 'enter';
                    e.y = -20;
                }
            }
        }

        // --- ufo bonus
        this._ufoAt -= dt;
        if (!this._ufo && this._ufoAt <= 0) {
            const dir = Math.random() < 0.5 ? 1 : -1;
            this._ufo = { x: dir > 0 ? -20 : this.W + 20, y: 32, v: dir * (55 + 15 * d), w: 20, h: 10 };
            this._ufoAt = 12 + Math.random() * 12;
        }
        if (this._ufo) {
            this._ufo.x += this._ufo.v * dt;
            if (this._ufo.x < -30 || this._ufo.x > this.W + 30) this._ufo = null;
        }

        // --- bullet vs enemy / ufo
        for (const b of this._bullets) {
            for (const e of this._enemies) {
                if (Math.abs(b.x - e.x) < e.w / 2 + 2 && Math.abs(b.y - e.y) < e.h / 2 + 3) {
                    b.y = -99;
                    this.killEnemy(e);
                    break;
                }
            }
            if (this._ufo && Math.abs(b.x - this._ufo.x) < 12 && Math.abs(b.y - this._ufo.y) < 8) {
                b.y = -99;
                this.addScore(100);
                this.explode(this._ufo.x, this._ufo.y, '#faD054');
                this._ufo = null;
            }
        }
        this._enemies = this._enemies.filter((e) => !e.dead);

        // --- enemy bullets / divers vs player
        if (p.invuln <= 0 && !p.dead) {
            for (const b of this._enemyBullets) {
                if (Math.abs(b.x - p.x) < p.w / 2 - 2 && Math.abs(b.y - p.y) < p.h / 2 + 3) {
                    b.y = this.H + 99;
                    this.hitPlayer();
                    break;
                }
            }
            for (const e of this._enemies) {
                if (Math.abs(e.x - p.x) < (e.w + p.w) / 2 - 4 && Math.abs(e.y - p.y) < (e.h + p.h) / 2 - 2) {
                    this.killEnemy(e, false);
                    this.hitPlayer();
                    break;
                }
            }
        }
        this._enemies = this._enemies.filter((e) => !e.dead);

        // --- formation reached the ship: costs a life, formation resets up
        if (this._enemies.some((e) => e.mode === 'formation' && e.y > p.y - 26)) {
            this._formation.y = 0;
            this.hitPlayer();
        }

        // --- wave cleared
        if (this._enemies.length === 0) {
            this.addScore(50);
            this.spawnWave();
        }

        // --- particles
        for (const pt of this._particles) {
            pt.x += pt.vx * dt;
            pt.y += pt.vy * dt;
            pt.life -= dt;
        }
        this._particles = this._particles.filter((pt) => pt.life > 0);

        // --- stars
        for (const s of this._stars) {
            s.y += s.v * dt;
            if (s.y > this.H) { s.y = -2; s.x = Math.random() * this.W; }
        }
    },

    addScore(points) {
        this.score += points;
        if (this.score > this.hiscore) {
            this.hiscore = this.score;
            localStorage.setItem('mblan_hiscore', String(this.hiscore));
        }
    },

    killEnemy(e, score = true) {
        e.dead = true;
        if (score) this.addScore(e.type === 'b' ? 20 : 10);
        this.explode(e.x, e.y, e.type === 'b' ? '#b074e0' : '#7ed957');
    },

    explode(x, y, color) {
        for (let i = 0; i < 12; i++) {
            const a = Math.random() * Math.PI * 2;
            const v = 30 + Math.random() * 90;
            this._particles.push({ x, y, vx: Math.cos(a) * v, vy: Math.sin(a) * v, life: 0.4 + Math.random() * 0.3, color });
        }
    },

    hitPlayer() {
        const p = this._player;
        this.explode(p.x, p.y, '#e6ecf0');
        this.lives--;
        p.invuln = 2;
        if (this.lives <= 0) {
            p.dead = true;
            this.gameOver();
        }
    },

    gameOver() {
        this.over = true;
        this.running = false;

        // Hand the score to the account: cookie for the post-login sync, and a
        // direct sync when the player is already logged in.
        this.setCookie('mblan_score', this.score);
        this.setCookie('mblan_done', '1');

        if (config.sync?.authenticated) {
            fetch(config.sync.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.sync.csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ score: this.score, completed: true }),
            }).catch(() => {});
        }

        setTimeout(() => { this.open = true; }, 650);
    },

    draw() {
        const ctx = this._ctx;
        if (!ctx) return;

        ctx.fillStyle = '#05080c';
        ctx.fillRect(0, 0, this.W, this.H);

        // starfield
        for (const s of this._stars) {
            ctx.globalAlpha = 0.35 + (s.s / 2) * 0.5;
            ctx.fillStyle = '#e6ecf0';
            ctx.fillRect(s.x, s.y, s.s, s.s);
        }
        ctx.globalAlpha = 1;

        // ufo
        if (this._ufo && this._sprites.ufo?.complete) {
            ctx.drawImage(this._sprites.ufo, this._ufo.x - 10, this._ufo.y - 6, 20, 12);
        }

        // enemies (2-frame animation)
        for (const e of this._enemies) {
            const frame = Math.floor((e.anim || 0) * 3) % 2 + 1;
            const img = this._sprites['invader_' + e.type + frame];
            if (img?.complete) {
                ctx.drawImage(img, e.x - e.w / 2, e.y - e.h / 2, e.w, e.h);
            }
        }

        // bullets
        ctx.fillStyle = 'rgb(' + getComputedStyle(document.documentElement).getPropertyValue('--c-primary-400').trim().split(' ').join(',') + ')';
        for (const b of this._bullets) ctx.fillRect(b.x - 1, b.y - 4, 2, 6);
        ctx.fillStyle = '#e05a4a';
        for (const b of this._enemyBullets) ctx.fillRect(b.x - 1, b.y, 2, 6);

        // player
        const p = this._player;
        if (p && !p.dead && this._sprites.ship?.complete) {
            if (p.invuln <= 0 || Math.floor(p.invuln * 10) % 2 === 0) {
                ctx.drawImage(this._sprites.ship, p.x - p.w / 2, p.y - p.h / 2, p.w, p.h);
            }
        }

        // particles
        for (const pt of this._particles) {
            ctx.globalAlpha = Math.max(0, pt.life * 2);
            ctx.fillStyle = pt.color;
            ctx.fillRect(pt.x - 1, pt.y - 1, 2, 2);
        }
        ctx.globalAlpha = 1;
    },
});
