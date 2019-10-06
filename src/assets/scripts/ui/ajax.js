var bootbox   = require('bootbox');

export default (function () {
    if (typeof window.ui === 'undefined') {
        window.ui = {};
    }
    ui.ajax = {
        methods: {
            singleMenuNodeReload: {
                url: '/actions/render-menu-node-template',
                method: "POST"
            }
        },
        init: function(){
            //this.attachModuleContentLoadingViaAjaxOnMenuLinks();
            /**
             * TODO: keep in mind
             *  keeping menu open is not working with ajax module load
             *  widgets (quick) preselecting options does not work
             *  shuffler in my images does not work - the layout is broken but rest is just ok
             */
        },
        singleMenuNodeReload: function(menuNodeModuleName, returnNotification = false) {

            let menuNode = $('.sidebar-menu-node-element[data-menu-node-name^="' + menuNodeModuleName + '"]');

            if( "undefined" === typeof menuNodeModuleName ){
                throw("Menu node name was not defined");
            }

            if( 0 === menuNode.length ){
                throw('Menu node with name: ' + menuNodeModuleName + ' - was not found');
            }

            if( 1 < menuNode.length ){
                throw('More than one menu nodes with name: ' + menuNodeModuleName + ' were found.');
            }

            let data = {
                "menu_node_module_name": menuNodeModuleName
            };

            let url    = this.methods.singleMenuNodeReload.url;
            let method = this.methods.singleMenuNodeReload.method;

            $.ajax({
                url: url,
                method: method,
                data: data,
            }).always((data) => {

                let message          = data['message'];
                let code             = data['code'];
                let tpl              = data['tpl'];
                let notificationType = ( code == 200 ? "success" : "danger" );

                if( "undefined" === typeof message ){
                    return;
                }

                if( '' !== tpl ){
                    $(menuNode).replaceWith(tpl);
                    window.sidebar.links.init();
                }

                if(returnNotification){
                    bootstrap_notifications.notify(message, notificationType)
                }

            });

        },
        attachModuleContentLoadingViaAjaxOnMenuLinks: function(){

            let _this = this;
            let excludeHrefsRegexPatterns = [
                /^javascript.*/g,
                /^#.*/g
            ];

            let allSidebarLinks = $('.sidebar-menu .sidebar-link');

            $.each(allSidebarLinks, (index, link) => {
                let href = $(link).attr('href');
                let skip = false;

                $.each(excludeHrefsRegexPatterns, (index, pattern) => {
                    if( null !== href.match(pattern) ){
                        skip = true;
                    }
                });

                if ( !skip && "" == href ){
                    skip = true;
                }

                if( !skip ){

                    $(link).on('click', (event) => {

                        event.preventDefault();
                        _this.loadModuleContentByUrl(href);

                    })

                }

            })



        },
        /**
         * This method will fetch module template and load it into mainBody
         */
        loadModuleContentByUrl: function (url){

            ui.widgets.loader.toggleLoader();

            $.ajax({
                url: url,
                method: "GET",
            }).always((data) => {

                $('.twig-body-section').html(data);
                initializer.reinitialize();

                ui.widgets.loader.toggleLoader();
                history.pushState({}, null, url);
            });

        }
    };
}());
