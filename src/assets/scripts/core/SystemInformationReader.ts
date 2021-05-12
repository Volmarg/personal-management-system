/**
 * @description handles reading system information provided in special hidden tag
 */
export default class SystemInformationReader {
    static readonly SYSTEM_INFORMATION_WRAPPER_TAG_ID = "systemInformation";
    static readonly SYSTEM_IS_LOCKED_SELECTOR         = "[data-is-system-locked]";

    /**
     * @description will return information if the system is locked or not
     */
    public static isSystemLocked(): boolean
    {
        let $wrapper = SystemInformationReader.getSystemInformationWrapperTag();
        let isLocked = Boolean($wrapper.find(SystemInformationReader.SYSTEM_IS_LOCKED_SELECTOR).val());

        return isLocked;
    }

    /**
     * @description will return the wrapper tag which consist of all of the settings
     */
    private static getSystemInformationWrapperTag(): JQuery<HTMLElement>
    {
        return $(`#${SystemInformationReader.SYSTEM_INFORMATION_WRAPPER_TAG_ID}`);
    }

}