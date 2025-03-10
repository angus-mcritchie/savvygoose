import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import barcode from './data/barcode';

Alpine.data('barcode', barcode);

Livewire.start();
