import * as $       from "jquery";
import Masonry      from 'masonry-layout';
import DomElements  from "../../core/utils/DomElements";

export default class MasonryGallery {

    /**
     * Will initialize Masonry gallery on elements
     */
    public static init(): void
    {

        let masonryTargets = $('.masonry');
        if( !DomElements.doElementsExists(masonryTargets) ){
            return;
        }

        new Masonry('.masonry', {
            itemSelector: '.masonry-item',
            columnWidth: '.masonry-sizer',
            percentPosition: true,
        });
    }

}