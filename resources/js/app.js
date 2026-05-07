import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import barcode from './data/barcode';
import base64Encoder from './data/base64Encoder';
import browserInfo from './data/browserInfo';
import caseConverter from './data/caseConverter';
import characterCounter from './data/characterCounter';
import colorConverter from './data/colorConverter';
import diffViewer from './data/diffViewer';
import hashGenerator from './data/hashGenerator';
import imageResizer from './data/imageResizer';
import jsonFormatter from './data/jsonFormatter';
import jwtDecoder from './data/jwtDecoder';
import loremIpsum from './data/loremIpsum';
import markdownConverter from './data/markdownConverter';
import passwordGenerator from './data/passwordGenerator';
import percentageDifferenceOfXAndY from './data/percentageDifferenceOfXAndY';
import qrCodeGenerator from './data/qrCodeGenerator';
import regexTester from './data/regexTester';
import slugGenerator from './data/slugGenerator';
import timeBetweenDates from './data/timeBetweenDates';
import timestampConverter from './data/timestampConverter';
import unitConverter from './data/unitConverter';
import urlEncoder from './data/urlEncoder';
import uuidGenerator from './data/uuidGenerator';
import xPercentOfY from './data/xPercentOfY';
import xPercentageOfY from './data/xPercentageOfY';
import xPlusOrMinusYPercent from './data/xPlusOrMinusYPercent';
import yIsXPercentOfWhat from './data/yIsXPercentOfWhat';
import mask from '@alpinejs/mask';
import { registerClipboard } from './lib/clipboard';
import { registerDownload } from './lib/download';

registerClipboard(Alpine);
registerDownload(Alpine);

Alpine.data('barcode', barcode);
Alpine.data('base64Encoder', base64Encoder);
Alpine.data('browserInfo', browserInfo);
Alpine.data('caseConverter', caseConverter);
Alpine.data('characterCounter', characterCounter);
Alpine.data('colorConverter', colorConverter);
Alpine.data('diffViewer', diffViewer);
Alpine.data('hashGenerator', hashGenerator);
Alpine.data('imageResizer', imageResizer);
Alpine.data('jsonFormatter', jsonFormatter);
Alpine.data('jwtDecoder', jwtDecoder);
Alpine.data('loremIpsum', loremIpsum);
Alpine.data('markdownConverter', markdownConverter);
Alpine.data('passwordGenerator', passwordGenerator);
Alpine.data('percentageDifferenceOfXAndY', percentageDifferenceOfXAndY);
Alpine.data('qrCodeGenerator', qrCodeGenerator);
Alpine.data('regexTester', regexTester);
Alpine.data('slugGenerator', slugGenerator);
Alpine.data('timeBetweenDates', timeBetweenDates);
Alpine.data('timestampConverter', timestampConverter);
Alpine.data('unitConverter', unitConverter);
Alpine.data('urlEncoder', urlEncoder);
Alpine.data('uuidGenerator', uuidGenerator);
Alpine.data('xPercentOfY', xPercentOfY);
Alpine.data('xPercentageOfY', xPercentageOfY);
Alpine.data('xPlusOrMinusYPercent', xPlusOrMinusYPercent);
Alpine.data('yIsXPercentOfWhat', yIsXPercentOfWhat);

Alpine.plugin(mask);

Alpine.magic('formatBytes', () => (bytes) => {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let v = bytes;
    while (v >= 1024 && i < units.length - 1) {
        v /= 1024;
        i++;
    }
    return `${v.toFixed(v >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
});

Livewire.start();
