
import SystemLockDialogs        from "../ui/Dialogs/SystemLockDialogs";
import LockedResourceAjaxCall   from "../locked-resource/LockedResourceAjaxCall";

export default class LockedResource {

    /**
     * @type LockedResourceAjaxCall
     */
    private lockedResourceAjaxCall = new LockedResourceAjaxCall();

    /**
     * @type Object
     */
    private elements = {
        'saved-element-class': '.save-parent',
    };

    /**
     * @type Object
     */
    private attributes = {
        dataToggleResourcesLockForSystem : 'data-toggle-resources-lock-for-system',
        dataSetResourcesLockForSystem    : 'data-set-resources-lock-password-for-system',
    };

    /**
     * @type SystemLockDialogs
     */
    private systemLockDialogs = new SystemLockDialogs();

    public init(){
        this.attachToggleRecordLockOnActionLockRecord();
        this.attachEventsOnToggleResourcesLockForSystem();
        this.attachEventsOnLockCreatePasswordForSystem();
    };

    /**
     * Adds click event on every lock record action icon
     */
    public attachToggleRecordLockOnActionLockRecord() {
        let _this              = this;
        let lockResourceButton = $('.action-lock-record');

        $(lockResourceButton).off('click'); // to prevent double attachement on reinit
        $(lockResourceButton).on('click', function () {
            let closest_parent = this.closest(_this.elements["saved-element-class"]);
            _this.lockedResourceAjaxCall.ajaxToggleLockRecord(closest_parent);
        });
    };

    /**
     * Attaches event in the user menu Lock button
     */
    public attachEventsOnToggleResourcesLockForSystem(){
        let _this   = this;
        let $button = $("[" + this.attributes.dataToggleResourcesLockForSystem + "= true]");

        $button.off('click');
        $button.on('click', function() {
            let $svg       = $button.find('svg');
            let isUnlocked = $svg.hasClass("text-success");

            if( isUnlocked ){
                LockedResourceAjaxCall.ajaxToggleSystemLock("", isUnlocked);
                return;
            }

            _this.systemLockDialogs.buildSystemToggleLockDialog(null, isUnlocked);
        });
    };

    /**
     * Attaches event for creating the first time password for lock when user does not have any set
     *  this is pretty much like needed due to the fact that there was no such option in old version of project
     */
    public attachEventsOnLockCreatePasswordForSystem(){
        let $button = $("[" + this.attributes.dataSetResourcesLockForSystem + "= true]");

        $button.off('click');
        $button.on('click', () => {
            this.systemLockDialogs.buildCreateLockPasswordForSystemDialog();
        });
    };

}