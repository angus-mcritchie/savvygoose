import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import barcode from './data/barcode';
import characterCounter from './data/characterCounter';

Alpine.data('barcode', barcode);
Alpine.data('characterCounter', characterCounter);


Livewire.start();
