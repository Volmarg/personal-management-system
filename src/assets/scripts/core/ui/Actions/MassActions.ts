export default class MassActions {

    static readonly MASS_ACTION_CHECKBOX_ATTRIBUTE_NAME = "data-is-mass-action-checkbox";

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
    private getAllCheckedCheckboxes(): JQuery<HTMLElement>
    {
        let $allCheckboxes = $("[" + MassActions.MASS_ACTION_CHECKBOX_ATTRIBUTE_NAME +"]:checked");
        return $allCheckboxes;
    }

}