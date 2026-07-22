import jsyaml from 'js-yaml';
import Papa from 'papaparse';
import XMLBuilder from 'fast-xml-builder';
import { XMLParser } from 'fast-xml-parser';

import { withUrlState } from '../lib/urlState';

const MAX_URL_INPUT = 3000;

const FORMATS = ['json', 'yaml', 'csv', 'xml'];

const FORMAT_LABELS = {
    json: 'JSON',
    yaml: 'YAML',
    csv: 'CSV',
    xml: 'XML',
};

const FORMAT_EXTENSIONS = {
    json: 'json',
    yaml: 'yml',
    csv: 'csv',
    xml: 'xml',
};

const FORMAT_MIME = {
    json: 'application/json',
    yaml: 'application/yaml',
    csv: 'text/csv',
    xml: 'application/xml',
};

const DELIMITERS = {
    comma: ',',
    tab: '\t',
    semicolon: ';',
    pipe: '|',
};

const SAMPLE_DATA = [
    { name: 'Hover Goose', founded: 2025, free: true, tags: ['utility', 'browser'] },
    { name: 'Barcode Builder', founded: 2024, free: true, tags: ['print', 'share'] },
    { name: 'JSON Lab', founded: 2023, free: false, tags: ['debug'] },
];

const SAMPLES = {
    json: JSON.stringify(SAMPLE_DATA, null, 2),
    yaml: jsyaml.dump(SAMPLE_DATA, { indent: 2, lineWidth: -1 }),
    csv: Papa.unparse(
        SAMPLE_DATA.map((row) => ({ ...row, tags: row.tags.join('|') })),
    ),
    xml: new XMLBuilder({ format: true, indentBy: '  ' }).build({
        items: { item: SAMPLE_DATA },
    }),
};

const schema = {
    from: { type: 'enum', values: FORMATS, default: 'json' },
    to: { type: 'enum', values: FORMATS, default: 'yaml' },
    indent: { type: 'enum', values: ['2', '4', 'tab'], default: '2' },
    delimiter: {
        type: 'enum',
        values: Object.keys(DELIMITERS),
        default: 'comma',
    },
    rootName: { type: 'string', default: 'root', maxLength: 64 },
    input: { type: 'string', maxLength: MAX_URL_INPUT },
};

// Papa's dynamicTyping is lossy: it turns "07030" into 7030, long ids past
// Number.MAX_SAFE_INTEGER lose precision, and "true"/"false" become booleans.
// Instead, keep every cell a string and only coerce numbers that survive a
// lossless round-trip, so zip codes, phone numbers, ISBNs, and IDs are safe.
export function coerceCell(value) {
    if (typeof value !== 'string' || value === '') return value;
    if (/^-?(0|[1-9]\d*)(\.\d+)?$/.test(value)) {
        const n = Number(value);
        if (Number.isFinite(n) && String(n) === value) return n;
    }
    return value;
}

function parseCsv(text, delimiter) {
    const result = Papa.parse(text, {
        header: true,
        skipEmptyLines: true,
        delimiter,
        dynamicTyping: false,
    });
    const fatal = (result.errors || []).find(
        (e) => e.type !== 'FieldMismatch',
    );
    if (fatal) throw new Error(`CSV parse error: ${fatal.message}`);
    return result.data.map((row) => {
        if (!row || typeof row !== 'object') return row;
        const out = {};
        for (const [key, val] of Object.entries(row)) out[key] = coerceCell(val);
        return out;
    });
}

function buildCsv(value, delimiter) {
    let rows = value;
    if (rows == null) rows = [];
    if (!Array.isArray(rows)) rows = [rows];
    rows = rows.map((row) => {
        if (row && typeof row === 'object' && !Array.isArray(row)) return row;
        return { value: row };
    });
    return Papa.unparse(rows, { delimiter });
}

function buildXml(value, indentStr, rootName) {
    const builder = new XMLBuilder({
        ignoreAttributes: false,
        attributeNamePrefix: '@_',
        format: true,
        indentBy: indentStr,
        suppressEmptyNode: true,
    });

    let toBuild;
    if (value == null) {
        toBuild = { [rootName || 'root']: '' };
    } else if (Array.isArray(value)) {
        toBuild = { [rootName || 'root']: { item: value } };
    } else if (typeof value !== 'object') {
        toBuild = { [rootName || 'root']: value };
    } else {
        const keys = Object.keys(value);
        if (keys.length === 1) {
            toBuild = value;
        } else {
            toBuild = { [rootName || 'root']: value };
        }
    }
    return builder.build(toBuild).replace(/\n$/, '');
}

export default withUrlState(schema, () => ({
    output: '',
    error: null,

    init() {
        ['from', 'to', 'indent', 'delimiter', 'rootName', 'input'].forEach(
            (prop) => this.$watch(prop, () => this.compute()),
        );
        this.compute();
    },

    compute() {
        const text = this.input;
        if (!text.trim()) {
            this.output = '';
            this.error = null;
            return;
        }

        try {
            const parsed = this.parseInput(text);
            this.output = this.serializeOutput(parsed);
            this.error = null;
        } catch (e) {
            this.output = '';
            this.error = { message: e.message || 'Conversion failed' };
        }
    },

    parseInput(text) {
        switch (this.from) {
            case 'json':
                return JSON.parse(text);
            case 'yaml':
                return jsyaml.load(text);
            case 'csv':
                return parseCsv(text, DELIMITERS[this.delimiter]);
            case 'xml': {
                const parser = new XMLParser({
                    ignoreAttributes: false,
                    attributeNamePrefix: '@_',
                    parseAttributeValue: true,
                    trimValues: true,
                });
                return parser.parse(text);
            }
            default:
                throw new Error(`Unknown source format: ${this.from}`);
        }
    },

    serializeOutput(value) {
        const indentStr =
            this.indent === 'tab' ? '\t' : ' '.repeat(parseInt(this.indent, 10));
        const indentNum = this.indent === 'tab' ? 1 : parseInt(this.indent, 10);

        switch (this.to) {
            case 'json':
                return JSON.stringify(
                    value,
                    null,
                    this.indent === 'tab' ? '\t' : indentNum,
                );
            case 'yaml':
                return jsyaml.dump(value, { indent: indentNum, lineWidth: -1 });
            case 'csv':
                return buildCsv(value, DELIMITERS[this.delimiter]);
            case 'xml':
                return buildXml(value, indentStr, this.rootName);
            default:
                throw new Error(`Unknown target format: ${this.to}`);
        }
    },

    swapDirection() {
        const oldOutput = this.output;
        const oldFrom = this.from;
        this.from = this.to;
        this.to = oldFrom;
        if (oldOutput) this.input = oldOutput;
    },

    swapToOutput() {
        if (!this.output) return;
        this.input = this.output;
        const oldFrom = this.from;
        this.from = this.to;
        this.to = oldFrom;
    },

    loadSample() {
        this.input = SAMPLES[this.from];
    },

    clear() {
        this.input = '';
    },

    get fromLabel() {
        return FORMAT_LABELS[this.from];
    },
    get toLabel() {
        return FORMAT_LABELS[this.to];
    },
    get downloadFilename() {
        return `data.${FORMAT_EXTENSIONS[this.to]}`;
    },
    get downloadMime() {
        return FORMAT_MIME[this.to];
    },
    get csvActive() {
        return this.from === 'csv' || this.to === 'csv';
    },
    get xmlTargetActive() {
        return this.to === 'xml';
    },
    get indentApplies() {
        return this.to === 'json' || this.to === 'yaml' || this.to === 'xml';
    },
    get inputBytes() {
        return new Blob([this.input]).size;
    },
    get outputBytes() {
        return new Blob([this.output]).size;
    },
}));
