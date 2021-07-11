var Encore                  = require('@symfony/webpack-encore');
var SWPrecacheWebpackPlugin = require('sw-precache-webpack-plugin');
var CopyPlugin              = require('copy-webpack-plugin');
var BundleAnalyzerPlugin    = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
var WebpackNotifierPlugin   = require('webpack-notifier');

/**
 * This is the standard configuration of Personal Management System UI
 */
Encore
    .addEntry('app', './src/assets/app.js') // will create public/build/app.js and public/build/app.css
    .addEntry('base-template', './src/assets/base-template.js')
    .addEntry('installer', './src/assets/vue/apps/Installer.ts')
    .setOutputPath('public/assets')         // the project directory where all compiled assets will be stored
    .setPublicPath('/assets')               // the public path used by the web server to access the previous directory
    .enableSassLoader()                     // allow sass/scss files to be processed
    .enableTypeScriptLoader(function (typeScriptConfigOptions) {
        typeScriptConfigOptions.transpileOnly = true;
        typeScriptConfigOptions.configFile    = 'tsconfig.json';
    })
    .enableVueLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild() // empty the outputPath dir before each build
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
        Popper: ['popper.js', 'default']
    }).addPlugin(
        new SWPrecacheWebpackPlugin(
        {
            cacheId: 'Personal_Management_System',
            dontCacheBustUrlsMatching: /\.\w{8}\./,
            filename: 'service-worker.js',
            minify: true,
            navigateFallback: 'index.html',
            staticFileGlobsIgnorePatterns: [/\.map$/, /asset-manifest\.json$/],
        })
    ).addPlugin(
        new CopyPlugin([
            /*
                Info: This MUST be copied for tinymce to work properly...
                it doesnt throw any errors if csses are missing yet without them
                in this location - tinymce wont work correctly
             */
            {from: './src/assets/scss/libs/tinmce/skins/ui/oxide',                  to: 'skins/ui/oxide'},
            {from: './src/assets/scss/libs/tinmce/skins/ui/oxide/content.css',      to: 'skins/content/default/content.css'},
            {from: './src/assets/scss/libs/tinymce-editor.css',                     to: 'css/tinymce-editor.css'},

            /**
             * Copying assets
              */
            {from: './src/assets/static/images/logo',                        to: 'images/logo'},
            {from: './src/assets/static/images/bcgk.jpg',                    to: 'images/bcgk.jpg'},
            {from: './src/assets/static/images/volmarg_avatar.jpg',          to: 'images/volmarg_avatar.jpg'},
            {from: './src/assets/static/images/avatar_placeholder.jpg',      to: 'images/avatar_placeholder.jpg'},

            /**
             * Other
             */
            {from: './src/assets/scripts/libs/fontawesome-picker/src/iconpicker-1.5.0.json',      to: 'libs/iconpicker-1.5.0.json'}, // required for fontawesome picker
        ])
    )
    // Turn on to analyse the weight of files, why the finall bundle is so big etc
    // This is turned off by default as it uses way to much resources upon each compliation
    // .addPlugin(
    //     // see: https://digitalfortress.tech/debug/how-to-use-webpack-analyzer-bundle/
    //     new BundleAnalyzerPlugin()
    // )
    .addPlugin(
        new WebpackNotifierPlugin({
            title: "Webpack",
            emoji: true,
        })
    )
    .enableBuildNotifications()
    .enableSingleRuntimeChunk();

// this skips some check in the TS while compiling files to speed up compilation
if( !Encore.isProduction() ){
    Encore.enableForkedTypeScriptTypesChecking();
}

const app_js_build = Encore.getWebpackConfig();

app_js_build.name    = 'app_js_build';
app_js_build.resolve = {
    extensions: [ '.tsx', '.ts', '.js' ],
};
app_js_build.stats                      = {};
app_js_build.stats.errors                = !Encore.isProduction();
app_js_build.optimization               = {}; // this reset is needed
app_js_build.optimization.namedModules  = !Encore.isProduction();

// this must be set only for the development, otherwise production mode compiles the assets like in the dev
if( !Encore.isProduction() ){
    app_js_build.devtool = 'eval-source-map';
}


// export the final configuration
module.exports = [app_js_build];
