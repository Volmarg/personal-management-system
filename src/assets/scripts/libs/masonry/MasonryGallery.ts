import * as $ from "jquery";
import Masonry from 'masonry-layout';

export default class MasonryGallery {

    /**
     * Will initialize Masonry gallery on elements
     */
    public static init(): void
    {

        let masonryTargets = $('.masonry');
        if( 0 === masonryTargets.length ){
            return;
        }

        new Masonry('.masonry', {
            itemSelector: '.masonry-item',
            columnWidth: '.masonry-sizer',
            percentPosition: true,
        });
    }

}