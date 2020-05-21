export default(function(){
    window.bootbox_extension = {
        /**
         * When modal is being called from within bootbox it looses all the focus as bootbox has tabindex = -1
         */
        removeTabindexFromActiveModals: function(){
            let $allActiveBootboxesModals = $('.bootbox.modal');

            $allActiveBootboxesModals.each(function(index, modal){
               let $modal = $(modal);
               $modal.removeAttr('tabindex');
            });
        },
        /**
         * The removed tabindex should be restored otherwise it wont be closable by hitting ESC
         */
        restoreTabindexForActiveModals: function(){
            let $allActiveBootboxesModals = $('.bootbox.modal');

            $allActiveBootboxesModals.each(function(index, modal){
                let $modal = $(modal);
                $modal.attr('tabindex', '-1');
            });
        },
    }
}());




