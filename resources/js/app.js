import './bootstrap';
import.meta.glob(['../images/**','../fonts/**',]);
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
window.Alpine = Alpine;
window.Chart = Chart;
Alpine.start();