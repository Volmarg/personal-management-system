import * as $ from "jquery";
import MasonryGallery from '../../libs/masonry/MasonryGallery';

export default class WindowEvents {

    public attachEvents()
    {
        this.attachLoadEvents();
    }

    private attachLoadEvents()
    {
        window.addEventListener('load', () => {
            if ($('.masonry').length > 0) {
                MasonryGallery.init();
            }
        });
    }
}