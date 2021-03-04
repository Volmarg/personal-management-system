import AbstractAction   from "./AbstractAction";
import Loader           from "../../../libs/loader/Loader";
import Navigation       from "../../Navigation";
import AjaxResponseDto  from "../../../DTO/AjaxResponseDto";

export default class ToggleBoolvalAction extends AbstractAction {

    public init()
    {
        this.attachToggleBoolvalEvent();
    }

    /**
     * Will call logic for handling inverting boolval in entity via ajax
     */
    private attachToggleBoolvalEvent(){
        let _this        = this;
        let $allElements = $("[" + this.data.entityToggleBoolval + "=true]");

        $allElements.off('click');
        $allElements.on('click', function(event){
            let $clickedElement = $(event.currentTarget);

            let repositoryName = $clickedElement.attr(_this.data.entityRepositoryName);
            let successMessage = $clickedElement.attr(_this.data.entityToggleBoolvalSuccessMessage);
            let fieldName      = $clickedElement.attr(_this.data.entityFieldName);
            let entityId       = $clickedElement.attr(_this.data.entityId);

            Loader.showMainLoader();

            $.ajax({
                url    : _this.methods.toggleBoolval.buildUrl(entityId, repositoryName, fieldName),
                method : _this.methods.toggleBoolval.method
            }).always(function(data){
                Loader.hideMainLoader();

                let ajaxResponseDto = AjaxResponseDto.fromArray(data);

                if( !ajaxResponseDto.isSuccessCode() ) {
                    _this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
                    return;
                }

                if( "undefined" !== typeof successMessage ){
                    _this.bootstrapNotify.showGreenNotification(successMessage);
                }else{
                    _this.bootstrapNotify.showGreenNotification(ajaxResponseDto.message);
                }

                _this.ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());

                if( ajaxResponseDto.reloadPage ){
                    if( ajaxResponseDto.isReloadMessageSet() ){
                        _this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                    }
                    location.reload();
                }
            });
        });
    };

}