import 'lightgallery';
import 'lightgallery/modules/lg-thumbnail'
import 'lightgallery/modules/lg-zoom'
import 'lightgallery/dist/css/lightgallery.min.css'
import 'lightgallery/dist/css/lg-transitions.min.css'

export default (function () {

    if (typeof window.gallery === 'undefined') {
        window.gallery = {};
    }

    gallery.lightgallery = {

        init: function () {

            if( $('#aniimated-thumbnials').length > 0 ){

                $('#aniimated-thumbnials').lightGallery({
                    thumbnail: true
                });

            }

        }
    }

}());


