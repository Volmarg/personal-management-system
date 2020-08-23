import * as $       from "jquery";
import Masonry      from 'masonry-layout';
import DomElements  from "../../core/utils/DomElements";

export default class MasonryGallery {

    /**
     * @description Will initialize Masonry gallery on elements
     */
    public static init(): void
    {

        let masonryTargets = $('.masonry');
        if( !DomElements.doElementsExists(masonryTargets) ){
            return;
        }

        /**
         * @description This try/catch block is required due to Masonry throwing exception which actually doesn't
         *              cause any problems. Exception can be fixed by `import * from Masonry` but then the dashboard
         *              won't be working as it relies on Masonry as well, therefore the catch block is added just to
         *              mute the give exception
         */
        try{
            new Masonry('.masonry', {
                itemSelector: '.masonry-item',
                columnWidth: '.masonry-sizer',
                percentPosition: true,
            });
        }catch(Exception){
            // mute
        }

    }

}