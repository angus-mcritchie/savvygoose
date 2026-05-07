// Clipboard primitives.
//
// Registers Alpine.store('copy') tracking which keys have just been copied,
// and Alpine.magic('copy') performing the actual write + flash.
//
// Usage:
//   $copy(text, 'mykey')            — writes text, flashes 'mykey' for 1.5s.
//   $store.copy.is('mykey')          — true while the flash is active.

const FLASH_MS = 1500;

export function registerClipboard(Alpine) {
    Alpine.store('copy', {
        flashed: {},

        flash(key) {
            if (!key) return;
            this.flashed[key] = (this.flashed[key] || 0) + 1;
            const token = this.flashed[key];
            setTimeout(() => {
                if (this.flashed[key] === token) this.flashed[key] = 0;
            }, FLASH_MS);
        },

        is(key) {
            return !!this.flashed[key];
        },
    });

    Alpine.magic('copy', () => async (text, key = '') => {
        if (text == null) return;
        const value = String(text);
        if (!value) return;

        try {
            await navigator.clipboard.writeText(value);
        } catch {
            const ta = document.createElement('textarea');
            ta.value = value;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch {}
            document.body.removeChild(ta);
        }

        Alpine.store('copy').flash(key);
    });
}
