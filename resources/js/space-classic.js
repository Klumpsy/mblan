/**
 * De editie-klassieker: "Arti in Space" — een Galaxian-achtige, oneindige
 * shooter. Arti en de boer delen een schip; golven aliens vliegen in
 * wisselende formaties binnen (hoe verder, hoe dichter, sneller en wilder)
 * en elke derde golf brengt een willekeurige BOSS met eigen gedrag: een
 * deelnemer van de LAN, of Darth Arti zelf. Namen staan onder de boss.
 * Besturing: slepen/muis of pijltjes (A/D); schieten gaat vanzelf.
 * Game over zet de mblan_score cookie; ingelogde spelers syncen direct.
 */
import { entryAmplitude, formationSlots, isBossWave, pickBoss } from './space-classic/registry';

export default (config = {}) => ({
    W: 320,
    H: 480,

    score: 0,
    hiscore: 0,
    wave: 0,
    lives: 3,
    running: false,
    over: false,
    open: false,
    bossName: null,

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
    _boss: null,
    _laser: null,
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
        this.bossName = null;
        this._bullets = [];
        this._enemyBullets = [];
        this._particles = [];
        this._ufo = null;
        this._boss = null;
        this._laser = null;
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
        const bossWave = isBossWave(this.wave);
        const amp = entryAmplitude(this.wave);

        this._enemies = formationSlots(this.wave, this.W, bossWave).map((slot, i) => ({
            slotX: slot.x,
            slotY: slot.y,
            x: slot.x,
            y: -20 - (i % 6) * 18,
            w: 18, h: 12,
            type: slot.type,
            mode: 'enter',
            t: Math.random() * Math.PI * 2,
            enterAmp: amp,
            diveX: 0,
        }));

        if (bossWave) this.spawnBoss();

        this._formation = { dir: 1, x: 0, y: 0, speed: 20 + 8 * this.difficulty() };
        this._diveAt = 2.5;
        this._ufoAt = 8 + Math.random() * 10;
    },

    spawnBoss(def = null) {
        def = def || pickBoss(Math.random());
        this.bossName = def.name;
        this._boss = {
            def,
            x: this.W / 2,
            y: -30,
            baseY: 44,
            w: 34, h: 22,
            hp: Math.round(def.hp * (1 + this.wave * 0.08)),
            maxHp: Math.round(def.hp * (1 + this.wave * 0.08)),
            t: 0,
            attackAt: 1.6,
            specialAt: 3,
            phase: 'enter',
            dashX: null,
            vx: 60, vy: 26,
            flash: 0,
            shielded: false,
        };
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
        for (const b of this._enemyBullets) {
            b.y += (b.vy ?? b.v) * dt;
            b.x += (b.vx || 0) * dt;
            if (b.bounce && (b.x < 2 || b.x > this.W - 2)) b.vx *= -1;
            if (b.life !== undefined) b.life -= dt;
            if (b.explodeY && b.y >= b.explodeY) {
                b.y = this.H + 99;
                for (let i = 0; i < 6; i++) {
                    const a = (i / 6) * Math.PI * 2;
                    this._enemyBullets.push({ x: b.x, y: b.explodeY, vx: Math.cos(a) * 70, vy: Math.sin(a) * 70 + 40, size: 2 });
                }
                this.explode(b.x, b.explodeY, '#f09434');
            }
        }
        this._enemyBullets = this._enemyBullets.filter((b) => b.y < this.H + 10 && b.y > -20 && (b.life === undefined || b.life > 0));

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
                e.t += dt;
                e.y += 90 * dt;
                // curved fly-in: wilder op latere golven
                const prog = Math.max(0, Math.min(1, e.y / Math.max(1, e.slotY + f.y)));
                e.x = e.slotX + f.x + Math.sin(e.t * 4) * e.enterAmp * (1 - prog);
                if (e.y >= e.slotY + f.y) e.mode = 'formation';
            } else if (e.mode === 'formation') {
                e.x = e.slotX + f.x;
                e.y = e.slotY + f.y;
                if (Math.random() < dt * 0.06 * d) {
                    this._enemyBullets.push({ x: e.x, y: e.y + 8, vy: 90 + 45 * d, size: 2 });
                }
            } else if (e.mode === 'dive') {
                e.t += dt;
                e.y += (120 + 40 * d) * dt;
                e.x = e.diveX + Math.sin(e.t * 3.2) * 46;
                if (Math.random() < dt * 0.5) {
                    this._enemyBullets.push({ x: e.x, y: e.y + 8, vy: 110 + 50 * d, size: 2 });
                }
                if (e.y > this.H + 20) {
                    e.mode = 'enter';
                    e.t = 0;
                    e.y = -20;
                }
            }
        }

        // --- boss
        if (this._boss) this.updateBoss(dt, d);
        if (this._laser) this.updateLaser(dt);

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

        // --- bullet vs enemy / ufo / boss
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
            const boss = this._boss;
            if (boss && boss.phase !== 'enter'
                && Math.abs(b.x - boss.x) < boss.w / 2 + 2 && Math.abs(b.y - boss.y) < boss.h / 2 + 3) {
                b.y = -99;
                this.hitBoss(boss);
            }
        }
        this._enemies = this._enemies.filter((e) => !e.dead);

        // --- enemy bullets / divers / boss vs player
        if (p.invuln <= 0 && !p.dead) {
            for (const b of this._enemyBullets) {
                const s = b.size || 2;
                if (Math.abs(b.x - p.x) < p.w / 2 - 2 + s && Math.abs(b.y - p.y) < p.h / 2 + 1 + s) {
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
            const boss = this._boss;
            if (boss && Math.abs(boss.x - p.x) < (boss.w + p.w) / 2 - 4 && Math.abs(boss.y - p.y) < (boss.h + p.h) / 2 - 2) {
                this.hitPlayer();
            }
        }
        this._enemies = this._enemies.filter((e) => !e.dead);

        // --- formation reached the ship: costs a life, formation resets up
        if (this._enemies.some((e) => e.mode === 'formation' && e.y > p.y - 26)) {
            this._formation.y = 0;
            this.hitPlayer();
        }

        // --- wave cleared (boss must be down too)
        if (this._enemies.length === 0 && !this._boss) {
            this.addScore(50);
            this.spawnWave();
        }

        // --- particles + stars
        for (const pt of this._particles) {
            pt.x += pt.vx * dt;
            pt.y += pt.vy * dt;
            pt.life -= dt;
        }
        this._particles = this._particles.filter((pt) => pt.life > 0);
        for (const s of this._stars) {
            s.y += s.v * dt;
            if (s.y > this.H) { s.y = -2; s.x = Math.random() * this.W; }
        }
    },

    updateBoss(dt, d) {
        const boss = this._boss;
        const def = boss.def;
        const p = this._player;
        boss.t += dt;
        if (boss.flash > 0) boss.flash -= dt;

        // --- entry, then movement per boss
        if (boss.phase === 'enter') {
            boss.y += 55 * dt;
            if (boss.y >= boss.baseY) boss.phase = 'fight';
            return;
        }

        const halfW = this.W / 2;
        switch (def.move) {
            case 'sine':
                boss.x = halfW + Math.sin(boss.t * 0.8) * (halfW - 40);
                boss.y = boss.baseY + Math.sin(boss.t * 1.7) * 6;
                break;
            case 'zigzag':
                boss.x = halfW + (Math.abs(((boss.t * 90) % (4 * (halfW - 40))) - 2 * (halfW - 40)) - (halfW - 40));
                boss.y = boss.baseY + Math.sin(boss.t * 5) * 10;
                break;
            case 'bounce':
                boss.x += boss.vx * dt;
                boss.y += boss.vy * dt;
                if (boss.x < 24 || boss.x > this.W - 24) boss.vx *= -1;
                if (boss.y < 30 || boss.y > this.H * 0.45) boss.vy *= -1;
                break;
            case 'teleport':
                if (boss.t > 1.6) {
                    this.explode(boss.x, boss.y, '#b074e0');
                    boss.x = 30 + Math.random() * (this.W - 60);
                    boss.y = 36 + Math.random() * 40;
                    boss.t = 0;
                }
                break;
            case 'dash':
                boss.y = boss.baseY;
                if (boss.dashX === null && boss.t > 2) {
                    boss.dashX = p.x;
                    boss.t = 0;
                }
                if (boss.dashX !== null) {
                    const dx = boss.dashX - boss.x;
                    boss.x += Math.sign(dx) * 240 * dt;
                    if (Math.abs(dx) < 8) boss.dashX = null;
                }
                break;
            case 'pounce':
                if (boss.t < 1.4) {
                    boss.y = boss.baseY + Math.sin(boss.t * 30) * 2; // trilt: telegraph
                } else if (boss.t < 2.2) {
                    boss.y += 300 * dt;
                    boss.x += Math.sign(p.x - boss.x) * 120 * dt;
                } else if (boss.y > boss.baseY) {
                    boss.y -= 160 * dt;
                } else {
                    boss.t = 0;
                }
                break;
            case 'swoop':
                boss.x = halfW + Math.sin(boss.t * 0.9) * (halfW - 44);
                boss.y = boss.baseY + Math.max(0, Math.sin(boss.t * 0.45) * (this.H * 0.55));
                break;
        }

        // --- attacks
        boss.attackAt -= dt;
        if (boss.attackAt <= 0) {
            this.bossAttack(boss, d);
            boss.attackAt = Math.max(0.5, 1.7 - d * 0.22);
        }

        // --- specials
        boss.specialAt -= dt;
        if (def.special === 'shield') {
            boss.shielded = Math.floor(boss.t / 2.5) % 2 === 1;
        }
        if (boss.specialAt <= 0) {
            if (def.special === 'summon') {
                for (let i = 0; i < 2; i++) {
                    this._enemies.push({
                        slotX: boss.x - 20 + i * 40, slotY: 110, x: boss.x, y: boss.y,
                        w: 18, h: 12, type: 'a', mode: 'enter', t: 0, enterAmp: 20, diveX: 0,
                    });
                }
            } else if (def.special === 'swarm') {
                for (let i = 0; i < 3; i++) {
                    this._enemies.push({
                        slotX: boss.x, slotY: 110, x: boss.x - 12 + i * 12, y: boss.y + 8,
                        w: 10, h: 8, type: 'b', mode: 'dive', t: i * 0.4, enterAmp: 10, diveX: boss.x,
                    });
                }
            }
            boss.specialAt = 4.5;
        }

        // --- force: Darth Arti trekt het schip naar zich toe
        if (def.special === 'force') {
            p.x += Math.sign(boss.x - p.x) * 34 * dt;
        }
    },

    bossAttack(boss, d) {
        const p = this._player;
        const from = { x: boss.x, y: boss.y + boss.h / 2 };

        switch (boss.def.attack) {
            case 'spread':
                for (let i = -2; i <= 2; i++) {
                    this._enemyBullets.push({ x: from.x, y: from.y, vx: i * 34, vy: 100 + 35 * d, size: 2 });
                }
                break;
            case 'aimed': {
                const a = Math.atan2(p.y - from.y, p.x - from.x);
                const v = 130 + 45 * d;
                this._enemyBullets.push({ x: from.x, y: from.y, vx: Math.cos(a) * v, vy: Math.sin(a) * v, size: 2 });
                break;
            }
            case 'rapid':
                for (let i = 0; i < 3; i++) {
                    this._enemyBullets.push({ x: from.x - 6 + i * 6, y: from.y + i * 6, vy: 150 + 40 * d, size: 2 });
                }
                break;
            case 'heavy':
                this._enemyBullets.push({ x: from.x, y: from.y, vx: Math.sin(boss.t) * 20, vy: 70 + 20 * d, size: 5 });
                break;
            case 'ricochet':
                this._enemyBullets.push({ x: from.x, y: from.y, vx: (Math.random() < 0.5 ? -1 : 1) * (90 + 25 * d), vy: 85 + 25 * d, size: 3, bounce: true });
                break;
            case 'venom':
                this._enemyBullets.push({ x: from.x, y: from.y, vx: (Math.random() - 0.5) * 30, vy: 35, size: 4, life: 7 });
                break;
            case 'bomb':
                this._enemyBullets.push({ x: from.x, y: from.y, vy: 90, size: 4, explodeY: from.y + 120 + Math.random() * 100 });
                break;
            case 'rain':
                for (let i = 0; i < 6; i++) {
                    this._enemyBullets.push({ x: (this.W / 7) * (i + 0.5 + Math.random() * 0.5), y: boss.y, vy: 95 + 30 * d, size: 2 });
                }
                break;
            case 'laser':
                if (!this._laser) this._laser = { x: boss.x, t: 0, telegraph: 0.8, active: 0.55 };
                break;
            case 'none':
                break;
        }
    },

    updateLaser(dt) {
        const l = this._laser;
        l.t += dt;
        const p = this._player;
        if (l.t > l.telegraph && l.t < l.telegraph + l.active) {
            if (p.invuln <= 0 && !p.dead && Math.abs(p.x - l.x) < 8 + p.w / 2 - 4) {
                this.hitPlayer();
            }
        }
        if (l.t >= l.telegraph + l.active) this._laser = null;
    },

    hitBoss(boss) {
        if (boss.shielded) {
            this.explode(boss.x, boss.y - boss.h / 2, '#6edcbe');
            return;
        }
        boss.hp--;
        boss.flash = 0.1;
        if (boss.hp <= 0) {
            this.addScore(250 + this.wave * 20);
            for (let i = 0; i < 4; i++) {
                this.explode(boss.x - 12 + Math.random() * 24, boss.y - 8 + Math.random() * 16, '#faD054');
            }
            this._boss = null;
            this._laser = null;
            this.bossName = null;
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

        for (const s of this._stars) {
            ctx.globalAlpha = 0.35 + (s.s / 2) * 0.5;
            ctx.fillStyle = '#e6ecf0';
            ctx.fillRect(s.x, s.y, s.s, s.s);
        }
        ctx.globalAlpha = 1;

        if (this._ufo && this._sprites.ufo?.complete) {
            ctx.drawImage(this._sprites.ufo, this._ufo.x - 10, this._ufo.y - 6, 20, 12);
        }

        for (const e of this._enemies) {
            const frame = Math.floor((e.anim || 0) * 3) % 2 + 1;
            const img = this._sprites['invader_' + e.type + frame];
            if (img?.complete) {
                ctx.drawImage(img, e.x - e.w / 2, e.y - e.h / 2, e.w, e.h);
            }
        }

        // boss + naam + hp
        const boss = this._boss;
        if (boss) {
            const img = this._sprites['boss_' + boss.def.key];
            if (boss.shielded) {
                ctx.strokeStyle = 'rgba(110,220,190,0.8)';
                ctx.beginPath();
                ctx.arc(boss.x, boss.y, boss.w / 2 + 6, 0, Math.PI * 2);
                ctx.stroke();
            }
            if (img?.complete) {
                ctx.globalAlpha = boss.flash > 0 ? 0.45 : 1;
                ctx.drawImage(img, boss.x - boss.w / 2, boss.y - boss.h / 2, boss.w, boss.h);
                ctx.globalAlpha = 1;
            }
            // naam onder de boss
            ctx.font = '6px "Press Start 2P", monospace';
            ctx.textAlign = 'center';
            ctx.fillStyle = '#e6ecf0';
            ctx.fillText(boss.def.name.toUpperCase(), boss.x, boss.y + boss.h / 2 + 10);
            // hp-balk
            const bw = 30;
            ctx.fillStyle = 'rgba(230,236,240,0.25)';
            ctx.fillRect(boss.x - bw / 2, boss.y - boss.h / 2 - 7, bw, 3);
            ctx.fillStyle = '#e05a4a';
            ctx.fillRect(boss.x - bw / 2, boss.y - boss.h / 2 - 7, bw * (boss.hp / boss.maxHp), 3);
        }

        // laser (telegraph dun, actief breed)
        if (this._laser) {
            const l = this._laser;
            if (l.t < l.telegraph) {
                ctx.fillStyle = 'rgba(224,90,74,0.35)';
                ctx.fillRect(l.x - 1, 0, 2, this.H);
            } else {
                ctx.fillStyle = 'rgba(224,90,74,0.85)';
                ctx.fillRect(l.x - 5, 0, 10, this.H);
            }
        }

        // bullets
        ctx.fillStyle = 'rgb(' + getComputedStyle(document.documentElement).getPropertyValue('--c-primary-400').trim().split(' ').join(',') + ')';
        for (const b of this._bullets) ctx.fillRect(b.x - 1, b.y - 4, 2, 6);
        for (const b of this._enemyBullets) {
            const s = b.size || 2;
            ctx.fillStyle = b.life !== undefined ? 'rgba(126,217,87,0.8)' : '#e05a4a';
            ctx.fillRect(b.x - s / 2, b.y, s, s + 2);
        }

        const p = this._player;
        if (p && !p.dead && this._sprites.ship?.complete) {
            if (p.invuln <= 0 || Math.floor(p.invuln * 10) % 2 === 0) {
                ctx.drawImage(this._sprites.ship, p.x - p.w / 2, p.y - p.h / 2, p.w, p.h);
            }
        }

        for (const pt of this._particles) {
            ctx.globalAlpha = Math.max(0, pt.life * 2);
            ctx.fillStyle = pt.color;
            ctx.fillRect(pt.x - 1, pt.y - 1, 2, 2);
        }
        ctx.globalAlpha = 1;
    },
});
