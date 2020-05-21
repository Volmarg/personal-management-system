var Encore = require('@symfony/webpack-encore');
var SWPrecacheWebpackPlugin = require('sw-precache-webpack-plugin');
var CopyPlugin = require('copy-webpack-plugin');

/**
 * This is the standard configuration of Personal Management System UI
 */
Encore
    .addEntry('app', './src/app.js') // will create public/build/app.js and public/build/app.css
    .setOutputPath('public/assets') // the project directory where all compiled assets will be stored
    .setPublicPath('/assets') // the public path used by the web server to access the previous directory
    .enableSassLoader() // allow sass/scss files to be processed
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
        {from: './src/scss/libs/tinmce/skins/ui/oxide',                  to: 'skins/ui/oxide'},
        {from: './src/scss/libs/tinmce/skins/ui/oxide/content.css',      to: 'skins/content/default/content.css'},
        {from: './src/scss/libs/tinymce-editor.css',                     to: 'css/tinymce-editor.css'},

        /**
         * Copying assets
          */

        {from: './src/assets/static/images/logo',                        to: 'images/logo'},
        {from: './src/assets/static/images/bcgk.jpg',                    to: 'images/bcgk.jpg'},
        {from: './src/assets/static/images/volmarg_avatar.jpg',          to: 'images/volmarg_avatar.jpg'},
        {from: './src/assets/static/images/avatar_placeholder.jpg',          to: 'images/avatar_placeholder.jpg'},
    ])
)
    .enableBuildNotifications();

const app_js_build = Encore.getWebpackConfig();
app_js_build.name = 'app_js_build';

/**
 * This is my modification for all the custom js scripts
 * I'm not using it as webpack will require some longer configurations
 * With this i can make 2nd file compiled independently (wanted to save time) but it creates another
 * js file with bunch of duplicated code from the output above so You end with 2 files (10mb/ea)
 */

/*Encore
    .addEntry('volmargCustomizations', './src/volmargCustomizations.js')
    .setOutputPath('public/assets')
    .setPublicPath('/assets')
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
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
)
    .enableBuildNotifications();

const volmargCustomizations = Encore.getWebpackConfig();
volmargCustomizations.name = 'volmargCustomizations';*/

// export the final configuration
module.exports = [app_js_build];
