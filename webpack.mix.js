const mix = require('laravel-mix');
const path = require('path');
const webpack = require('webpack');

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/messages.js', 'public/js')
    .vue({ version: 3 })
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ])
    .webpackConfig({
        resolve: {
            symlinks: false, // 1. Disable symlinks to prevent path confusion on Windows
            modules: [
                path.resolve(__dirname, 'node_modules'), // 2. Prioritize root node_modules
                'node_modules'
            ],
            alias: {
                // 3. FORCE FLATTEN VUE DEPENDENCIES
                // This tells Webpack: "No matter who asks for these, give them the root version."
                'vue$': path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js'),
                '@vue/runtime-dom$': path.resolve(__dirname, 'node_modules/@vue/runtime-dom/dist/runtime-dom.esm-bundler.js'),
                '@vue/runtime-core$': path.resolve(__dirname, 'node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js'),
                '@vue/reactivity$': path.resolve(__dirname, 'node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js'),
                '@vue/shared$': path.resolve(__dirname, 'node_modules/@vue/shared/dist/shared.esm-bundler.js'),
                '@vue/compiler-sfc$': path.resolve(__dirname, 'node_modules/@vue/compiler-sfc/dist/compiler-sfc.esm-browser.js'),

                // 4. THE GLOBAL KILL-SWITCH (Previous Fix)
                'laravel-mix': false,
                'yargs': false,
                'webpack': false,
                'postcss-load-config': false,
                'postcss-load-config/src/req.js': false,

                // 5. Node Polyfills
                'fs': false,
                'path': require.resolve('path-browserify'),
                'os': require.resolve('os-browserify/browser'),
                'stream': require.resolve('stream-browserify'),
                'crypto': require.resolve('crypto-browserify'),
                'constants': require.resolve('constants-browserify'),
            },
            fallback: {
                "process": require.resolve("process/browser"),
                "buffer": require.resolve("buffer/"),
                "child_process": false,
                "util": false,
                "assert": false,
            }
        },
        plugins: [
            new webpack.ProvidePlugin({
                process: 'process/browser',
                Buffer: ['buffer', 'Buffer'],
            }),
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: true,
                __VUE_PROD_DEVTOOLS__: false,
            }),
            new webpack.IgnorePlugin({
                resourceRegExp: /^\.\/locale$/,
                contextRegExp: /moment$/
            })
        ]
    });
