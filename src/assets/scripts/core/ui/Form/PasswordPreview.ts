/**
 * @description handles the possibility of showing/hiding raw password in input
 * @link https://codepen.io/mhdnauvalazhar/pen/XWbEQmv
 */
export default class PasswordPreview
{
    static readonly INPUT_TYPE_PASSWORD = "password";
    static readonly INPUT_TYPE_TEXT     = "text";

    /**
     * @description will initialize the logic
     */
    public init(): void
    {
        this.attachEventToTogglePasswordVisibility();
    }

    /**
     * @description will attach event to toggle password visibility
     */
    private attachEventToTogglePasswordVisibility(): void
    {
        let $allElementsToHandle = $('[data-toggle="password"]');
        $allElementsToHandle.each( (index, element) => {

            let $element = $(element);
            $element.on('click', (event) => {
                event.preventDefault();

                let $inputElement = $element.closest('.password-preview-wrapper').find('input');
                $inputElement.focus();

                let currentType = $inputElement.attr('type');
                if (currentType == PasswordPreview.INPUT_TYPE_PASSWORD) {
                    $inputElement.attr('type', PasswordPreview.INPUT_TYPE_TEXT);
                } else {
                    $inputElement.attr('type', PasswordPreview.INPUT_TYPE_PASSWORD);
                }
            });
        })
    }
}