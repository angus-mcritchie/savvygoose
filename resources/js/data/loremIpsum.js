const DEFAULTS = {
    type: 'paragraphs',
    count: 3,
    classic: true,
};

const WORDS = [
    'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
    'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
    'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
    'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'ex', 'ea', 'commodo',
    'consequat', 'duis', 'aute', 'irure', 'in', 'reprehenderit', 'voluptate',
    'velit', 'esse', 'cillum', 'eu', 'fugiat', 'nulla', 'pariatur', 'excepteur',
    'sint', 'occaecat', 'cupidatat', 'non', 'proident', 'sunt', 'culpa', 'qui',
    'officia', 'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum', 'curabitur',
    'pretium', 'tincidunt', 'lacus', 'nulla', 'gravida', 'orci', 'a', 'odio',
    'nullam', 'varius', 'turpis', 'molestie', 'volutpat', 'placerat', 'erat',
    'quam', 'pharetra', 'magnis', 'dis', 'parturient', 'montes', 'nascetur',
    'ridiculus', 'mus', 'aenean', 'donec', 'felis', 'hendrerit', 'tellus',
    'mauris', 'arcu', 'libero', 'congue', 'tristique', 'sapien', 'massa',
];

const CLASSIC_OPENING = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

function xmur3(str) {
    let h = 1779033703 ^ str.length;
    for (let i = 0; i < str.length; i++) {
        h = Math.imul(h ^ str.charCodeAt(i), 3432918353);
        h = (h << 13) | (h >>> 19);
    }
    return () => {
        h = Math.imul(h ^ (h >>> 16), 2246822507);
        h = Math.imul(h ^ (h >>> 13), 3266489909);
        h ^= h >>> 16;
        return h >>> 0;
    };
}

function mulberry32(seed) {
    let s = seed | 0;
    return () => {
        s = (s + 0x6d2b79f5) | 0;
        let t = Math.imul(s ^ (s >>> 15), 1 | s);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

function makeRng(seed) {
    return mulberry32(xmur3(String(seed))());
}

function pick(rng, arr) {
    return arr[Math.floor(rng() * arr.length)];
}

function randInt(rng, min, max) {
    return Math.floor(rng() * (max - min + 1)) + min;
}

function makeSentence(rng, length) {
    const words = [];
    for (let i = 0; i < length; i++) words.push(pick(rng, WORDS));
    let sentence = words.join(' ');
    const commaCount = length > 8 ? randInt(rng, 0, 2) : length > 5 ? randInt(rng, 0, 1) : 0;
    for (let i = 0; i < commaCount; i++) {
        const pos = randInt(rng, 1, length - 2);
        const parts = sentence.split(' ');
        parts[pos] = parts[pos] + ',';
        sentence = parts.join(' ');
    }
    return sentence[0].toUpperCase() + sentence.slice(1) + '.';
}

function generateWords(rng, count) {
    const out = [];
    for (let i = 0; i < count; i++) out.push(pick(rng, WORDS));
    return out.join(' ');
}

function generateSentences(rng, count, classic) {
    const out = [];
    for (let i = 0; i < count; i++) {
        if (i === 0 && classic) {
            out.push(CLASSIC_OPENING);
        } else {
            out.push(makeSentence(rng, randInt(rng, 6, 14)));
        }
    }
    return out.join(' ');
}

function generateParagraphs(rng, count, classic) {
    const out = [];
    for (let i = 0; i < count; i++) {
        const sentenceCount = randInt(rng, 3, 7);
        const sentences = [];
        for (let j = 0; j < sentenceCount; j++) {
            if (i === 0 && j === 0 && classic) {
                sentences.push(CLASSIC_OPENING);
            } else {
                sentences.push(makeSentence(rng, randInt(rng, 6, 14)));
            }
        }
        out.push(sentences.join(' '));
    }
    return out.join('\n\n');
}

function freshSeed() {
    return Math.floor(Math.random() * 1_000_000_000).toString(36);
}

export default () => ({
    type: DEFAULTS.type,
    count: DEFAULTS.count,
    classic: DEFAULTS.classic,
    seed: freshSeed(),
    copied: false,
    url: window.location.href,

    init() {
        this.initFromUrl();
        ['type', 'count', 'classic', 'seed'].forEach((p) => this.$watch(p, () => this.updateUrl()));
        this.updateUrl();
    },

    get output() {
        const rng = makeRng(this.seed + ':' + this.type + ':' + this.count + ':' + this.classic);
        const count = Math.max(1, Math.min(100, parseInt(this.count, 10) || 1));
        if (this.type === 'words') return generateWords(rng, count);
        if (this.type === 'sentences') return generateSentences(rng, count, this.classic);
        return generateParagraphs(rng, count, this.classic);
    },

    regenerate() {
        this.seed = freshSeed();
    },

    async copy() {
        if (!this.output) return;
        await navigator.clipboard.writeText(this.output);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('type') && ['paragraphs', 'sentences', 'words'].includes(params.get('type'))) {
            this.type = params.get('type');
        }
        if (params.has('count')) {
            const n = parseInt(params.get('count'), 10);
            if (Number.isFinite(n) && n >= 1 && n <= 100) this.count = n;
        }
        if (params.has('classic')) this.classic = params.get('classic') === '1';
        if (params.has('seed')) this.seed = params.get('seed');
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.type !== DEFAULTS.type) params.set('type', this.type);
        else params.delete('type');

        const count = parseInt(this.count, 10) || DEFAULTS.count;
        if (count !== DEFAULTS.count) params.set('count', String(count));
        else params.delete('count');

        if (this.classic !== DEFAULTS.classic) params.set('classic', this.classic ? '1' : '0');
        else params.delete('classic');

        params.set('seed', this.seed);

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
