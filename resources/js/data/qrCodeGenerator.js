import QRCode from 'qrcode';

const DEFAULTS = {
    size: 256,
    ec: 'M',
    fg: '#000000',
    bg: '#ffffff',
    margin: 4,
    logoSize: 20,
    logoPadding: true,
};

const EC_LEVELS = ['L', 'M', 'Q', 'H'];
const MAX_LOGO_BYTES = 2 * 1024 * 1024;

// Heroicons solid 24x24, recolored at use-time via {color} placeholder.
const PRESET_ICONS = {
    url: {
        label: 'URL',
        path: '<path d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.389 4.267a.75.75 0 0 1 1-.353 5.25 5.25 0 0 1 1.449 8.45l-4.5 4.5a5.25 5.25 0 1 1-7.424-7.424l1.757-1.757a.75.75 0 1 1 1.06 1.06l-1.757 1.757a3.75 3.75 0 1 0 5.304 5.304l4.5-4.5a3.75 3.75 0 0 0-1.035-6.037.75.75 0 0 1-.354-1Z"/>',
    },
    wifi: {
        label: 'Wi-Fi',
        path: '<path d="M1.371 8.143c5.858-5.857 15.356-5.857 21.213 0a.75.75 0 0 1 0 1.061l-.53.53a.75.75 0 0 1-1.06 0c-4.98-4.979-13.053-4.979-18.032 0a.75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM4.555 11.328c4.098-4.098 10.749-4.098 14.847 0a.75.75 0 0 1 0 1.06l-.53.53a.75.75 0 0 1-1.06 0 8.25 8.25 0 0 0-11.667 0 .75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM7.738 14.512a6 6 0 0 1 8.484 0 .75.75 0 0 1 0 1.06l-.53.53a.75.75 0 0 1-1.061 0 3.75 3.75 0 0 0-5.303 0 .75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM12 17.625a1.5 1.5 0 0 0-1.06.44.75.75 0 0 1-1.061-1.06 3 3 0 0 1 4.242 0 .75.75 0 0 1-1.06 1.06A1.5 1.5 0 0 0 12 17.625Z"/>',
    },
    contact: {
        label: 'Contact',
        path: '<path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"/>',
    },
    email: {
        label: 'Email',
        path: '<path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>',
    },
    phone: {
        label: 'Phone',
        path: '<path d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z"/>',
    },
    location: {
        label: 'Location',
        path: '<path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>',
    },
};

export default () => ({
    text: '',
    size: DEFAULTS.size,
    ec: DEFAULTS.ec,
    fg: DEFAULTS.fg,
    bg: DEFAULTS.bg,
    margin: DEFAULTS.margin,
    logo: null,
    logoSize: DEFAULTS.logoSize,
    logoPadding: DEFAULTS.logoPadding,
    logoError: null,
    activePreset: null,
    presets: PRESET_ICONS,
    renderToken: 0,
    url: window.location.href,
    contrastWarning: false,

    init() {
        this.initFromUrl();

        ['text', 'size', 'ec', 'fg', 'bg', 'margin'].forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.render();
            });
        });

        ['logo', 'logoSize', 'logoPadding'].forEach((prop) => {
            this.$watch(prop, () => this.render());
        });

        this.$watch('fg', () => {
            if (this.activePreset) this.applyPreset(this.activePreset);
        });

        this.updateUrl();
        this.$nextTick(() => this.render());
    },

    onLogoSelected(event) {
        const file = event.target.files?.[0];
        this.logoError = null;

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.logoError = 'That file is not an image.';
            event.target.value = '';
            return;
        }

        if (file.size > MAX_LOGO_BYTES) {
            this.logoError = 'Image is larger than 2 MB.';
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            this.activePreset = null;
            this.logo = reader.result;
        };
        reader.readAsDataURL(file);
    },

    applyPreset(key) {
        const preset = PRESET_ICONS[key];
        if (!preset) return;

        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${this.fg}">${preset.path}</svg>`;
        this.activePreset = key;
        this.logo = `data:image/svg+xml;base64,${btoa(svg)}`;
        if (this.$refs.logoInput) this.$refs.logoInput.value = '';
        this.logoError = null;
    },

    clearLogo() {
        this.logo = null;
        this.logoError = null;
        this.activePreset = null;
        if (this.$refs.logoInput) this.$refs.logoInput.value = '';
    },

    loadLogoImage() {
        if (!this.logo) return Promise.resolve(null);
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = this.logo;
        });
    },

    drawLogoOnCanvas(canvas, img) {
        if (!img) return;
        const ctx = canvas.getContext('2d');
        const ratio = Math.max(5, Math.min(40, parseInt(this.logoSize, 10))) / 100;
        const target = Math.round(canvas.width * ratio);

        const scale = Math.min(target / img.width, target / img.height);
        const drawW = img.width * scale;
        const drawH = img.height * scale;
        const cx = (canvas.width - drawW) / 2;
        const cy = (canvas.height - drawH) / 2;

        if (this.logoPadding) {
            const pad = Math.round(target * 0.12);
            ctx.fillStyle = this.bg;
            ctx.fillRect(cx - pad, cy - pad, drawW + pad * 2, drawH + pad * 2);
        }

        ctx.drawImage(img, cx, cy, drawW, drawH);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('text')) {
            this.text = params.get('text');
        }

        if (params.has('size')) {
            const parsed = parseInt(params.get('size'), 10);
            if (Number.isFinite(parsed) && parsed >= 64 && parsed <= 2048) {
                this.size = parsed;
            }
        }

        if (params.has('ec')) {
            const ec = params.get('ec').toUpperCase();
            if (EC_LEVELS.includes(ec)) {
                this.ec = ec;
            }
        }

        if (params.has('fg') && /^#?[0-9a-fA-F]{6}$/.test(params.get('fg'))) {
            this.fg = '#' + params.get('fg').replace('#', '');
        }

        if (params.has('bg') && /^#?[0-9a-fA-F]{6}$/.test(params.get('bg'))) {
            this.bg = '#' + params.get('bg').replace('#', '');
        }

        if (params.has('margin')) {
            const parsed = parseInt(params.get('margin'), 10);
            if (Number.isFinite(parsed) && parsed >= 0 && parsed <= 16) {
                this.margin = parsed;
            }
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.text) {
            params.set('text', this.text);
        } else {
            params.delete('text');
        }

        if (this.size !== DEFAULTS.size) {
            params.set('size', this.size);
        } else {
            params.delete('size');
        }

        if (this.ec !== DEFAULTS.ec) {
            params.set('ec', this.ec);
        } else {
            params.delete('ec');
        }

        if (this.fg.toLowerCase() !== DEFAULTS.fg) {
            params.set('fg', this.fg.replace('#', ''));
        } else {
            params.delete('fg');
        }

        if (this.bg.toLowerCase() !== DEFAULTS.bg) {
            params.set('bg', this.bg.replace('#', ''));
        } else {
            params.delete('bg');
        }

        if (parseInt(this.margin, 10) !== DEFAULTS.margin) {
            params.set('margin', this.margin);
        } else {
            params.delete('margin');
        }

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },

    options() {
        return {
            errorCorrectionLevel: this.ec,
            margin: parseInt(this.margin, 10),
            color: { dark: this.fg, light: this.bg },
            width: parseInt(this.size, 10),
        };
    },

    async render() {
        const canvas = this.$refs.canvas;
        if (!canvas) return;

        const token = ++this.renderToken;

        if (!this.text) {
            canvas.width = parseInt(this.size, 10) || DEFAULTS.size;
            canvas.height = canvas.width;
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            this.contrastWarning = false;
            return;
        }

        try {
            const img = await this.loadLogoImage();
            if (token !== this.renderToken) return;

            const off = document.createElement('canvas');
            await QRCode.toCanvas(off, this.text, this.options());
            if (token !== this.renderToken) return;

            this.drawLogoOnCanvas(off, img);

            canvas.width = off.width;
            canvas.height = off.height;
            canvas.getContext('2d').drawImage(off, 0, 0);

            this.contrastWarning = this.computeContrast(this.fg, this.bg) < 3;
        } catch (err) {
            console.error('QR render failed:', err);
        }
    },

    async downloadPng() {
        if (!this.text) return;
        const canvas = document.createElement('canvas');
        await QRCode.toCanvas(canvas, this.text, this.options());
        const img = await this.loadLogoImage();
        this.drawLogoOnCanvas(canvas, img);
        canvas.toBlob((blob) => this.triggerDownload(blob, 'qr-code.png'));
    },

    async downloadSvg() {
        if (!this.text) return;
        let svg = await QRCode.toString(this.text, { ...this.options(), type: 'svg' });

        if (this.logo) {
            const img = await this.loadLogoImage();
            const ratio = Math.max(5, Math.min(40, parseInt(this.logoSize, 10))) / 100;
            const viewBoxMatch = svg.match(/viewBox="([^"]+)"/);
            const viewBox = viewBoxMatch ? viewBoxMatch[1].split(/\s+/).map(Number) : [0, 0, 100, 100];
            const vbW = viewBox[2];
            const target = vbW * ratio;
            const scale = Math.min(target / img.width, target / img.height);
            const drawW = img.width * scale;
            const drawH = img.height * scale;
            const cx = (vbW - drawW) / 2 + viewBox[0];
            const cy = (vbW - drawH) / 2 + viewBox[1];

            const overlay = [];
            if (this.logoPadding) {
                const pad = target * 0.12;
                overlay.push(`<rect x="${cx - pad}" y="${cy - pad}" width="${drawW + pad * 2}" height="${drawH + pad * 2}" fill="${this.bg}"/>`);
            }
            overlay.push(`<image href="${this.logo}" x="${cx}" y="${cy}" width="${drawW}" height="${drawH}" preserveAspectRatio="xMidYMid meet"/>`);
            svg = svg.replace('</svg>', overlay.join('') + '</svg>');
        }

        const blob = new Blob([svg], { type: 'image/svg+xml' });
        this.triggerDownload(blob, 'qr-code.svg');
    },

    triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },

    computeContrast(fg, bg) {
        const lum = (hex) => {
            const c = hex.replace('#', '');
            const [r, g, b] = [0, 2, 4].map((i) => parseInt(c.slice(i, i + 2), 16) / 255);
            const adj = (v) => (v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
            return 0.2126 * adj(r) + 0.7152 * adj(g) + 0.0722 * adj(b);
        };
        const l1 = lum(fg);
        const l2 = lum(bg);
        const [light, dark] = l1 > l2 ? [l1, l2] : [l2, l1];
        return (light + 0.05) / (dark + 0.05);
    },
});
