const mix = require('laravel-mix');

const fs = require('fs-extra');
const replace = require('replace-in-file');
const uid = require('uid');

require('laravel-mix-alias');

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

mix
    // JS
    .js('resources/assets/js/buddy/app.js', 'public/js/app.js')
//     .js('resources/assets/js/rush/app.js', 'public/js/rush.js')
    .js('resources/assets/js/_general/lib/photo_moderation.js', 'public/backend/js/photo_moderation.js')
    .js('resources/assets/js/_general/lib/video_moderation.js', 'public/backend/js/video_moderation.js')

    // STYLES
    .styles('public/assets/css/app.css', 'public/assets/css/out/app.min.css')
//     .sass('resources/assets/sass/rush.scss', 'public/css/rush.min.css')
    .sass('resources/assets/sass/buddy/new/main.scss', 'public/new/css/main.min.css')
    .sass('resources/assets/sass/buddy/main/app.scss', 'public/main/css/app.min.css')
    .styles([
        'public/assets/css/desktop.css',
        'public/assets/css/custom-desktop.css',
    ], 'public/assets/css/out/desktop.min.css')
    .styles([
        'public/assets/css/mobile.css',
        'public/assets/css/custom-mobile.css',
    ], 'public/assets/css/out/mobile.min.css')
    .styles('public/assets/css/custom-common.css', 'public/assets/css/out/custom-common.min.css')

    // COPIES
    .copy('node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js', 'public/backend/plugins/datepicker')
    .copy('node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css', 'public/backend/plugins/datepicker')
    .copy('node_modules/select2/dist/js/select2.min.js', 'public/backend/plugins/select2')
    .copy('node_modules/select2/dist/css/select2.min.css', 'public/backend/plugins/select2')

    // Add version hash
    .version()

    // Replace service-worker version
    .then(async function(){
        // There MUST be .tmp file
        // Otherwise browser might cache non-replaced version
        await fs.copy(
            // './public/service-worker.js.dist',
            // './public/service-worker.js.tmp',
            { overwrite: true }
        );

        let mixManifest = await fs.readJson('./public/mix-manifest.json')
        let versionAssets = [
            '/js/app.js',
            '/new/css/main.min.css',
            '/main/css/app.min.css',
            '/assets/css/out/app.min.css',
            '/assets/css/out/mobile.min.css',
            '/assets/css/out/desktop.min.css',
            '/assets/css/out/custom-common.min.css',
        ]

        versionAssets.forEach(function(url) {
            replace.sync({
                files: './public/service-worker.js.tmp',
                from: url,
                to: mixManifest[url]
            })
        })

        await replace({
            files: './public/service-worker.js.tmp',
            from: /%version%/g,
            to: uid()
        });

        await fs.move(
            './public/service-worker.js.tmp',
            './public/service-worker.js',
            { overwrite: true }
        );
    });

// Fix vue-swipe-actions ...this for Edge browser
mix.webpackConfig({
    devtool: 'source-map',
    module: {
        rules: [{
            test: /\.js?$/,
            exclude: /(node_modules\/(?!(vue-swipe-actions)\/)|bower_components)/,
            use: [{
                loader: 'babel-loader',
                options: mix.config.babel()
            }]
        }]
    }
});

// Aliases
mix.alias({
    '@general': '/resources/assets/js/_general',
    '@chat': '/resources/assets/js/chat',
    '@notifications': '/resources/assets/js/notifications',
    '@discover': '/resources/assets/js/discover',
    '@buddy': '/resources/assets/js/buddy',
    '@events': '/resources/assets/js/events',
    '@clubs': '/resources/assets/js/clubs',
    '@profile': '/resources/assets/js/profile',
    '@search': '/resources/assets/js/search'
//     '@rush': '/resources/assets/js/rush',
});
