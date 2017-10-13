const {mix} = require('laravel-mix');

/**
 * Mix assets
 */
mix.js('resources/assets/js/app.js', 'public/js')
    .less('resources/assets/less/app.less', 'public/css')
    .version();
