import StringUtils from "../../core/utils/StringUtils";
import DomElements from "../../core/utils/DomElements";

const passwordGenerator = require('generate-password');

/**
 * @description handles generating passwords
 * @link https://www.npmjs.com/package/generate-password
 */
export default class GeneratePassword
{

    static readonly DATA_ATTRIBUTE_TARGET_INPUT_SELECTOR = "data-target-input-selector";
    static readonly DATA_GENERATE_PASSWORD_ON_CLICK      = "data-generate-password-on-click";

    private static readonly MAX_PASSWORD_LENGTH = 20;

    /**
     * @description will initialize the logic
     */
    public init(): void
    {
        this.handleGeneratingPasswordOnButtonClick();
    }

    /**
     * @description will handle generating password and placing it into given input
     */
    private handleGeneratingPasswordOnButtonClick(): void
    {
        let _this               = this;
        let $allButtonsToHandle = $(`[${GeneratePassword.DATA_GENERATE_PASSWORD_ON_CLICK}]`);
        $allButtonsToHandle.each( (index, element) => {
            let $element            = $(element);
            let targetInputSelector = $element.attr(GeneratePassword.DATA_ATTRIBUTE_TARGET_INPUT_SELECTOR);

            if( !StringUtils.isEmptyString(targetInputSelector) ){
                let $targetInput = $(targetInputSelector);
                if( DomElements.doElementsExists($targetInput) ){

                    $element.off('click');
                    $element.on('click', () => {
                        let password = _this.generatePassword(10);
                        $targetInput.val(password);
                    })
                }
            }
        })
    }

    /**
     * @description will generate password of given length
     */
    private generatePassword(minPasswordLength: number, maxPasswordLength: number = GeneratePassword.MAX_PASSWORD_LENGTH): string
    {
        let passwordLength = Math.floor(Math.random() * (maxPasswordLength - minPasswordLength + 1)) + minPasswordLength;

        return passwordGenerator.generate({
            length    : passwordLength,
            lowercase : true,
            uppercase : true,
            symbols   : true,
            numbers   : true,
        });
    }

}