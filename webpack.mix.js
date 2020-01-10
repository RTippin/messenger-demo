const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    resolve: {
        alias: {
            'jquery': path.resolve('node_modules/jquery/src/jquery')
        }
    }
});
mix.options({
    cssNano: { normalizePositions: false }
});

mix.scripts(['resources/js/managers/ThreadManager.js'], 'public/js/managers/ThreadManager.js').version();
mix.scripts(['resources/js/templates/ThreadTemplates.js'], 'public/js/templates/ThreadTemplates.js').version();
mix.scripts(['resources/js/managers/NetworksManager.js'], 'public/js/managers/NetworksManager.js').version();
mix.scripts(['resources/js/managers/GuestManager.js'], 'public/js/managers/GuestManager.js').version();
mix.scripts(['resources/js/modules/Emoji.js'], 'public/js/modules/Emoji.js').version();
mix.js('resources/js/app.js', 'public/js').sass('resources/sass/app.scss', 'public/css').version();
mix.js('resources/js/emojione.js', 'public/js/modules').version();
mix.js('resources/js/managers/NotifyManager.js', 'public/js/managers').version();
mix.js('resources/js/managers/WebRTCManager.js', 'public/js/managers').version();
mix.sass('resources/sass/messages.scss', 'public/css').version();
mix.sass('resources/sass/calls.scss', 'public/css').version();
