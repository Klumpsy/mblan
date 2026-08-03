import { describe, expect, it } from 'vitest';
import {
    ATTACKS,
    BOSSES,
    MOVES,
    SPECIALS,
    entryAmplitude,
    formationSlots,
    isBossWave,
    pickBoss,
} from './registry';

describe('boss registry', () => {
    it('bevat alle deelnemers plus Darth Arti', () => {
        const names = BOSSES.map((b) => b.name);

        expect(BOSSES).toHaveLength(19);
        expect(names).toEqual(expect.arrayContaining([
            'Addy3528', 'Anne (BlueRaven)', 'Bart [Klumpsy]', 'Bas (TwenteWarrior)',
            'BramDaBeer', 'C.Orneel', 'Corneel (Corny)', 'FryingDutchPan',
            'Gido Akse', 'HeerserNiek', 'Jeroen (Bijbal)', 'Juan (WickedVenom)',
            'Kaasbal', 'Martin (MartN)', 'Rik', 'Sophie (Artix)',
            'Thomas (10ft_T)', 'Yannick (Yanusz)', 'Darth Arti',
        ]));
    });

    it('heeft unieke keys en namen', () => {
        expect(new Set(BOSSES.map((b) => b.key)).size).toBe(BOSSES.length);
        expect(new Set(BOSSES.map((b) => b.name)).size).toBe(BOSSES.length);
    });

    it('gebruikt alleen bestaande gedragingen', () => {
        for (const b of BOSSES) {
            expect(MOVES, `${b.key} move`).toContain(b.move);
            expect(ATTACKS, `${b.key} attack`).toContain(b.attack);
            expect(SPECIALS, `${b.key} special`).toContain(b.special);
            expect(b.hp).toBeGreaterThan(0);
        }
    });

    it('geeft elke boss een eigen gedrag-combinatie', () => {
        const combos = BOSSES.map((b) => `${b.move}|${b.attack}|${b.special}`);
        expect(new Set(combos).size).toBe(BOSSES.length);
    });

    it('Darth Arti heeft de force', () => {
        const vader = BOSSES.find((b) => b.key === 'arti-vader');
        expect(vader.special).toBe('force');
        expect(vader.hp).toBe(Math.max(...BOSSES.map((b) => b.hp)));
    });

    it('kiest deterministisch met een injectable rng', () => {
        expect(pickBoss(0)).toBe(BOSSES[0]);
        expect(pickBoss(0.999)).toBe(BOSSES[BOSSES.length - 1]);
    });
});

describe('golven en formaties', () => {
    it('elke derde golf is een boss-golf', () => {
        expect([1, 2, 4, 5].some(isBossWave)).toBe(false);
        expect([3, 6, 9, 30].every(isBossWave)).toBe(true);
    });

    it('latere golven brengen meer vijanden', () => {
        const early = formationSlots(1).length;
        const late = formationSlots(12).length;

        expect(late).toBeGreaterThan(early);
    });

    it('formaties worden dichter op elkaar naarmate je verder komt', () => {
        const gap = (slots) => {
            const xs = [...new Set(slots.map((s) => s.x))].sort((a, b) => a - b);
            return xs[1] - xs[0];
        };

        expect(gap(formationSlots(16))).toBeLessThan(gap(formationSlots(1)));
    });

    it('de lay-out wisselt per golf', () => {
        const shape = (slots) => JSON.stringify(slots.map((s) => [Math.round(s.x), Math.round(s.y)]));

        expect(shape(formationSlots(1))).not.toBe(shape(formationSlots(3)));
        expect(shape(formationSlots(3))).not.toBe(shape(formationSlots(4)));
    });

    it('escort-mobs op boss-golven zijn kleiner', () => {
        expect(formationSlots(6, 320, true).length).toBeLessThan(formationSlots(6).length);
    });

    it('alle slots blijven binnen het speelveld', () => {
        for (const wave of [1, 2, 3, 7, 15, 40]) {
            for (const s of formationSlots(wave, 320)) {
                expect(s.x).toBeGreaterThanOrEqual(0);
                expect(s.x).toBeLessThanOrEqual(320);
                expect(s.y).toBeGreaterThan(0);
                expect(['a', 'b']).toContain(s.type);
            }
        }
    });

    it('de invliegroutes worden wilder per golf', () => {
        expect(entryAmplitude(10)).toBeGreaterThan(entryAmplitude(1));
        expect(entryAmplitude(100)).toBeLessThanOrEqual(70);
    });
});
