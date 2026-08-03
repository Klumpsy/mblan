/**
 * Pure game data + formation math for the editie-klassieker. No DOM here, so
 * this file is unit-testable (Vitest) and shared with the canvas engine.
 */
import BOSSES from './bosses.json';

export { BOSSES };

export const MOVES = ['sine', 'swoop', 'dash', 'zigzag', 'bounce', 'teleport', 'pounce'];
export const ATTACKS = ['none', 'spread', 'aimed', 'rapid', 'heavy', 'ricochet', 'venom', 'bomb', 'rain', 'laser'];
export const SPECIALS = [null, 'summon', 'swarm', 'shield', 'force'];

/** Every 3rd wave is a boss wave: the boss brings a smaller escort mob. */
export function isBossWave(wave) {
    return wave > 0 && wave % 3 === 0;
}

/** Pick a boss for a wave; rng in [0,1) is injectable so tests are deterministic. */
export function pickBoss(rng = Math.random()) {
    return BOSSES[Math.floor(rng * BOSSES.length) % BOSSES.length];
}

/**
 * Formation slots for a wave: positions the mob flies into. Layouts rotate
 * per wave and the field grows/tightens the further you get, so later
 * formations are genuinely harder (more enemies, denser, deeper).
 *
 * @returns array of {x, y, type} within [0, width]
 */
export function formationSlots(wave, width = 320, escort = false) {
    const layouts = ['grid', 'checker', 'vee', 'diamond', 'pincer'];
    const layout = layouts[(wave - 1) % layouts.length];

    // density scales with the wave; escort mobs (boss waves) are smaller
    let rows = Math.min(6, 3 + Math.floor(wave / 3));
    let cols = Math.min(10, 8 + Math.floor(wave / 5));
    if (escort) {
        rows = Math.max(2, rows - 2);
    }

    const gapX = Math.max(24, 30 - Math.floor(wave / 4));
    const gapY = Math.max(18, 24 - Math.floor(wave / 5));
    const startX = (width - (cols - 1) * gapX) / 2;
    const topY = escort ? 96 : 60;

    const slots = [];
    const mid = (cols - 1) / 2;

    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            let x = startX + c * gapX;
            let y = topY + r * gapY;

            if (layout === 'checker') {
                if ((r + c) % 2 === 1) continue;
                y += (c % 2) * (gapY / 2);
            } else if (layout === 'vee') {
                y += Math.abs(c - mid) * (gapY * 0.6);
            } else if (layout === 'diamond') {
                const d = Math.abs(c - mid) + Math.abs(r - (rows - 1) / 2);
                if (d > mid) continue;
                x += (r % 2) * (gapX / 3);
            } else if (layout === 'pincer') {
                if (c > 2 && c < cols - 3) continue;
                y += r * (gapY * 0.35);
            }

            slots.push({ x, y, type: r < 2 ? 'b' : 'a' });
        }
    }

    return slots;
}

/** Entry curve amplitude: later waves fly in along wilder paths. */
export function entryAmplitude(wave) {
    return Math.min(70, 10 + wave * 6);
}
