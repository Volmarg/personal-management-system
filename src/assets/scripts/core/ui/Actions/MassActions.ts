/**
 * //tpdo: need to handle somehow that actions should be visible (not disabled) only when checkbox are selected
 *    - just bind an event to the checkbox - if any is selected - unlock mass actions
 */
import DomAttributes from "../../utils/DomAttributes";

export default class MassActions {

    static readonly MASS_ACTION_CHECKBOX_ATTRIBUTE_NAME = "data-is-mass-action-checkbox";

    /**
     * @description will initialize the main logic
     */
    public init()
    {
        this.attachMassActionToggleButtonAccessibilityOnCheckboxSelection();
    }

    /**
     * @description will return files paths for all currently selected checkboxes (only these related to mass action)
     *
     * @private
     */
    public getFilesPathsForAllSelectedCheckboxes(): Array<string>
    {
        let $allSelectedCheckboxes = this.getAllCheckedCheckboxes();
        let allFilesPaths          = [];

        $.each($allSelectedCheckboxes, (index, element) => {
            let $element        = $(element);
            let filePath        = $element.data('value').toString();
            let escapedFilePath = ( filePath.indexOf('/') === 0 ? filePath.replace("/", "") : filePath ) ;
            allFilesPaths.push(escapedFilePath);
        })

        return allFilesPaths;
    }

    /**
     * @description will return all currently selected checkboxes (only these related to mass action)
     *
     * @private
     */
    public getAllCheckedCheckboxes(): JQuery<HTMLElement>
    {
        let $allCheckboxes = $("[" + MassActions.MASS_ACTION_CHECKBOX_ATTRIBUTE_NAME +"]:checked");
        return $allCheckboxes;
    }

    /**
     * @description will set all mass actions to enabled
     */
    public enableAllMassActions(): void {
        let $allElements = this.getAllMassActionButtons();
        DomAttributes.unsetDisabledClass($allElements);
    }

    /**
     * @description will set all mass actions to disabled
     */
    public disableAllMassActions(): void {
        let $allElements = this.getAllMassActionButtons();
        DomAttributes.setDisabledClass($allElements);
    }

    /**
     * @description will attach event to the mass actions checkboxes
     *              if at least one is checked then mass actions are enabled
     *              otherwise actions remain disabled
     */
    public attachMassActionToggleButtonAccessibilityOnCheckboxSelection()
    {
        let $allCheckboxes = $("[" + MassActions.MASS_ACTION_CHECKBOX_ATTRIBUTE_NAME +"]");

        $allCheckboxes.off('click');
        $allCheckboxes.on('click', () => {
            let allCheckedCheckboxes = this.getAllCheckedCheckboxes();

            if(allCheckedCheckboxes.length >= 1){
                this.enableAllMassActions();
            }else{
                this.disableAllMassActions();
            }
        })
    }

    /**
     * @description Will return all mass action DOM elements
     */
    private getAllMassActionButtons(): JQuery<HTMLElement>
    {
        let $allElements = $('.mass-action');
        return $allElements;
    }
}