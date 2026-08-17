import { describe, it, expect } from 'vitest';
import { countdownParts } from './edition-countdown';

describe('countdownParts', () => {
  it('breaks a positive delta into days/hours/minutes/seconds', () => {
    const now = Date.UTC(2026, 8, 1, 0, 0, 0);
    const target = now + ((2 * 86400) + (3 * 3600) + (4 * 60) + 5) * 1000;
    expect(countdownParts(target, now)).toEqual({
      days: 2, hours: 3, minutes: 4, seconds: 5, done: false,
    });
  });

  it('clamps to zero and reports done at/after the target', () => {
    const now = Date.UTC(2026, 8, 1, 0, 0, 0);
    expect(countdownParts(now - 5000, now)).toEqual({
      days: 0, hours: 0, minutes: 0, seconds: 0, done: true,
    });
  });
});
