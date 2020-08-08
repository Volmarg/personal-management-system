import UiUtils from "../utils/UiUtils";

export default class DocumentEvents {

    /**
     * @type UiUtils
     */
    private uiUtils = new UiUtils();

    public attachEvents(){
        this.attachLoadedEvents();
    }

    private attachLoadedEvents()
    {
        document.addEventListener('DOMContentLoaded', () => {
            this.uiUtils.keepUploadBasedMenuOpen();
        });
    }
}