// Pure math: how much time is left, split into units. Deterministic — pass
// `nowMs` so it is unit-testable without touching the clock.
export function countdownParts(targetMs, nowMs) {
    const done = nowMs >= targetMs;
    let rest = Math.max(0, Math.floor((targetMs - nowMs) / 1000));

    const days = Math.floor(rest / 86400);
    rest -= days * 86400;
    const hours = Math.floor(rest / 3600);
    rest -= hours * 3600;
    const minutes = Math.floor(rest / 60);
    const seconds = rest - minutes * 60;

    return { days, hours, minutes, seconds, done };
}

// Alpine data: a live countdown to `target` (ISO 8601). Ticks each second and
// stops itself once the target passes.
export function editionCountdown({ target }) {
    return {
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        done: false,
        _timer: null,

        init() {
            const targetMs = new Date(target).getTime();

            const tick = () => {
                const p = countdownParts(targetMs, Date.now());
                this.days = p.days;
                this.hours = p.hours;
                this.minutes = p.minutes;
                this.seconds = p.seconds;
                this.done = p.done;

                if (p.done && this._timer) {
                    clearInterval(this._timer);
                    this._timer = null;
                }
            };

            tick();
            this._timer = setInterval(tick, 1000);
        },

        destroy() {
            if (this._timer) {
                clearInterval(this._timer);
            }
        },
    };
}
