import { describe, it, expect } from 'vitest';
import { makeStars, twinkle } from './space-stars';

// A deterministic stand-in for Math.random.
function seeded(seed = 1) {
  return () => {
    seed = (seed * 16807) % 2147483647;
    return (seed - 1) / 2147483646;
  };
}

describe('makeStars', () => {
  it('scatters the requested number of stars inside the canvas', () => {
    const stars = makeStars(120, 800, 600, seeded());
    expect(stars).toHaveLength(120);
    for (const s of stars) {
      expect(s.x).toBeGreaterThanOrEqual(0);
      expect(s.x).toBeLessThan(800);
      expect(s.y).toBeGreaterThanOrEqual(0);
      expect(s.y).toBeLessThan(600);
      expect(s.r).toBeGreaterThan(0);
      expect(s.depth).toBeGreaterThan(0);
      expect(s.depth).toBeLessThanOrEqual(1);
    }
  });

  it('is deterministic for the same random source', () => {
    expect(makeStars(10, 100, 100, seeded(7))).toEqual(makeStars(10, 100, 100, seeded(7)));
  });
});

describe('twinkle', () => {
  it('stays inside the given brightness band', () => {
    for (let t = 0; t < 20; t += 0.37) {
      const v = twinkle(t, 1.3, 0.35, 1);
      expect(v).toBeGreaterThanOrEqual(0.35);
      expect(v).toBeLessThanOrEqual(1);
    }
  });
});
