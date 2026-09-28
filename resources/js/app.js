import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import axios from 'axios';

import '../../vendor/step2dev/lazy-ui/resources/js/lazy.js';

window.Alpine ??= Alpine;
window.Livewire ??= Livewire;
window.axios ??= axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Livewire.start();
