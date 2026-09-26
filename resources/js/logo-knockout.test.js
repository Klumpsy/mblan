import { describe, it, expect } from 'vitest';
import { knockoutDark } from './logo-knockout';

describe('knockoutDark', () => {
  it('turns black and near-black pixels fully transparent', () => {
    const data = new Uint8ClampedArray([0, 0, 0, 255, 6, 6, 10, 255]);
    knockoutDark(data);
    expect(data[3]).toBe(0);
    expect(data[7]).toBe(0);
  });

  it('keeps white fully opaque and unchanged', () => {
    const data = new Uint8ClampedArray([255, 255, 255, 255]);
    knockoutDark(data);
    expect([...data]).toEqual([255, 255, 255, 255]);
  });

  it('un-premultiplies a colour that was composited over black', () => {
    // cyan at 80% over black: (0, 204, 204) -> pure cyan at ~80% alpha
    const data = new Uint8ClampedArray([0, 204, 204, 255]);
    knockoutDark(data);
    expect(data[0]).toBe(0);
    expect(data[1]).toBe(255);
    expect(data[2]).toBe(255);
    expect(data[3]).toBeGreaterThan(190);
    expect(data[3]).toBeLessThan(210);
  });

  it('never raises an alpha the source already had', () => {
    const data = new Uint8ClampedArray([255, 255, 255, 40]);
    knockoutDark(data);
    expect(data[3]).toBe(40);
  });
});
