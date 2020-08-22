import Shuffler    from './src/Shuffler';
import DomElements from "../../core/utils/DomElements";

export default class ShuffleWrapper {

    /**
     * @type Object
     */
   private selectors = {
        classes: {
            itemSelector   : '.grid-item',
            searchSelector : '.js-shuffle-search',
            tagsHolder     : '.shuffler-tags-options'
        },
        other: {
            galleryHolder  : "aniimated-thumbnials",
        }
   };

    /**
     * @type Shuffler
     */
   public shuffler = null;

    public init(){
        this.initShuffler();
    };

    public addTagsButtonsEvents()
    {
        this.shuffler.addTagsButtonsEvents();
    }

    public buildTagsArrayFromTagsForImages()
    {
        return this.shuffler.buildTagsArrayFromTagsForImages();
    }

    private initShuffler() {
        let _this = this;

        $(document).ready(() => {
            let domGalleryHolder = document.getElementById(_this.selectors.other.galleryHolder);
            let itemSelector     = _this.selectors.classes.itemSelector;
            let searchSelector   = _this.selectors.classes.searchSelector;

            if( undefined === domGalleryHolder || null === domGalleryHolder ){
                return;
            }

            _this.shuffler = new Shuffler(domGalleryHolder, itemSelector, searchSelector);
            _this.shuffler.init();

            let tagsArray = _this.shuffler.buildTagsArrayFromTagsForImages();
            _this.appendTagsToFilter(tagsArray);

            _this.shuffler.addTagsButtonsEvents();
        });
    };

    public appendTagsToFilter(tagsArray){
        let domTagsHolder = $(this.selectors.classes.tagsHolder);
        domTagsHolder.html('');

        //append each tag as option
        $.each(tagsArray, (index, tag) => {
            let button = $('<button>');
            button.addClass('btn btn-sm btn-primary');
            button.css({
                "margin": "2px",
                "height": '30px'
            });
            button.attr('data-group', tag);
            button.html(tag);

            $(domTagsHolder).append(button);
        });
    };

    public removeTagsFromFilter(){
        let domTagsHolder = $(this.selectors.classes.tagsHolder);
        $(domTagsHolder).html('');
    };

    public removeImageByDataUniqueId(id){
        let allIShuffleItems = this.shuffler.shuffle.items;
        let removedItem      = null;
        let removedElement   = null;

        $.each(allIShuffleItems, (index, item) => {

            let domElement = item.element;
            let uniqueId   = $(domElement).attr('data-unique-id');

            if( uniqueId == id){
                removedItem     = item;
                removedElement  = domElement;
            }

        });

        this.shuffler.shuffle.remove([removedElement]);
    };

    public switchToGroupAllIfGroupIsRemoved(){
        let selectedGroup        = this.shuffler.shuffle.group;
        let domTagsHolder        = $(this.selectors.classes.tagsHolder);
        let $correspondingButton = $(domTagsHolder).find('[data-group^="' + selectedGroup + '"]');

        if( !DomElements.doElementsExists($correspondingButton) ){
            this.shuffler.shuffle.filter("all");
        }

    }
}