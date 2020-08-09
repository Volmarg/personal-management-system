import * as $           from "jquery";
import MasonryGallery   from '../../libs/masonry/MasonryGallery';
import AjaxEvents       from "../ajax/AjaxEvents";
import Loader           from "../../libs/loader/Loader";

export default class WindowEvents {

    public attachEvents()
    {
        this.attachLoadEventsForMasonryGallery();
        this.attachLoadBasedEventsForLoader();
    }

    private attachLoadEventsForMasonryGallery()
    {
        window.addEventListener('load', () => {
            if ($('.masonry').length > 0) {
                MasonryGallery.init();
            }
        });
    }

    /**
     * @description Attaches events responsible for showing/hiding spinner/loader upon switch pages without ajax call
     */
    private attachLoadBasedEventsForLoader()
    {
        let ajaxEvents = new AjaxEvents();

        ajaxEvents.init();

        $(window).on('beforeunload', function(){
            Loader.showLoader();
        });

        $(window).on('load', function(){
            Loader.hideLoader();
        });

        let denyUnloadForSelectors = ['.file-download'];

        $.each(denyUnloadForSelectors, function(index, selector) {
            let $element = $(selector);
            $element.on('click', function(){
                setTimeout(function(){
                    Loader.hideLoader();
                }, 1000);
            })
        });

    }
}