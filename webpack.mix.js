const mix = require('laravel-mix');
const CompressionPlugin = require('compression-webpack-plugin');
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

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/bootstrap.js', 'public/js')
    .extract([
        'axios',
        'vue',
        'vuex',
        'lodash',
        'vuetify',
        'vue2-editor',
        'highcharts',
        'emoji-mart-vue',
        'graphql',
        'string-strip-html',
        'emoji-mart-vue-fast',
        'sweetalert2',
    ])
    .sass('resources/sass/app.scss', 'public/css')
    .styles([
        'resources/css/custom.css'
    ], 'public/css/app_custom.css').options({
    uglify: true,
    processCssUrls: false,
}).webpackConfig({
    node: {
        fs: 'empty'
    },
    plugins: [
        new CompressionPlugin({
            algorithm: 'gzip',
            test: /\.js$|\.css$|\.html$|\.svg$/,
            compressionOptions: { level: 9 },
            threshold: 10240,
            minRatio: 0.8,
        }),
    ],
}).version()
;
