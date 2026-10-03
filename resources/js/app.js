import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Alpine from 'alpinejs';

import registerCharts from './nutritrace/charts';
import registerComponents from './nutritrace/components';
import registerQr from './nutritrace/qr';
import registerWizard from './nutritrace/report-wizard';
import initReveal from './nutritrace/reveal';

document.documentElement.classList.add('js');

registerComponents(Alpine);
registerCharts(Alpine);
registerQr(Alpine);
registerWizard(Alpine);

window.Alpine = Alpine;

Alpine.start();

initReveal();
