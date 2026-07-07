import './bootstrap';

import Alpine from 'alpinejs';
import ujs from '@rails/ujs';

window.Alpine = Alpine;

Alpine.start();

// Подключаем UJS для обработки data-method
ujs.start();