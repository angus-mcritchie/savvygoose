import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import barcode from './data/barcode';
import characterCounter from './data/characterCounter';
import percentageDifferenceOfXAndY from './data/percentageDifferenceOfXAndY';
import xPercentOfY from './data/xPercentOfY';
import xPercentageOfY from './data/xPercentageOfY';
import mask from '@alpinejs/mask';

Alpine.data('barcode', barcode);
Alpine.data('characterCounter', characterCounter);
Alpine.data('percentageDifferenceOfXAndY', percentageDifferenceOfXAndY);
Alpine.data('xPercentOfY', xPercentOfY);
Alpine.data('xPercentageOfY', xPercentageOfY);

Alpine.plugin(mask);

Livewire.start();
