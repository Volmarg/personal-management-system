/**
 * @description handles the logic of tooltip visible on the right side of the interface
 */
import JsCookie from "../../libs/js-cookie/JsCookie";

export default class JsSettingsTooltip {

    private static readonly SETTINGS_COG_SELECTOR         = ".js-settings-toggle";
    private static readonly SETTINGS_PANEL_SELECTOR       = ".settings-panel";
    private static readonly SETTINGS_CLOSE_PANEL_SELECTOR = ".js-settings-close";

    private static readonly SETTINGS_THEME_COLOR_INPUT_SELECTOR = "input.theme-color";
    private static readonly BODY_DATA_ATTRIBUTE_THEME           = "data-theme";

    /**
     * @description will initialize the main logic
     */
    public init(): void
    {
        this.attachShowingTooltipToCogsIcon();
        this.handleClosingSettingsPanel();
        this.handleThemeSelect();
    }

    /**
     * @description will set the theme from cookie saved value
     */
    public setThemeFromCookie(): void
    {
        if( JsCookie.isJsSettingsSelectedTheme() ){
            this.setTheme(JsCookie.getJsSettingsSelectedTheme());
        }
    }

    /**
     * @description will attach the events/logic for showing the panel upon clicking on the cogs icon
     */
    private attachShowingTooltipToCogsIcon(): void
    {
        $(JsSettingsTooltip.SETTINGS_COG_SELECTOR).on('click', () => {
            $(JsSettingsTooltip.SETTINGS_PANEL_SELECTOR).addClass('settings-panel-shown');
        })
    }

    /**
     * @description attaches the logic responsible to hiding back the js settings panel
     */
    private handleClosingSettingsPanel(): void
    {
        $(JsSettingsTooltip.SETTINGS_CLOSE_PANEL_SELECTOR).on('click', () => {
            $(JsSettingsTooltip.SETTINGS_PANEL_SELECTOR).removeClass('settings-panel-shown');
        })
    }

    /**
     * @description will handle selecting theme
     *              - based on user click,
     *              - based on what's already stored in cookies,
     */
    private handleThemeSelect(): void
    {
        $(JsSettingsTooltip.SETTINGS_THEME_COLOR_INPUT_SELECTOR).on('click', (event) => {
            //@ts-ignore
            let selectedTheme = event.target.value;
            this.setTheme(selectedTheme);
        })
    }

    /**
     * @description will set given theme
     */
    private setTheme(themeName: string): void
    {
        $(JsSettingsTooltip.SETTINGS_THEME_COLOR_INPUT_SELECTOR).removeAttr('checked');
        $(JsSettingsTooltip.SETTINGS_THEME_COLOR_INPUT_SELECTOR).prop("checked", false);

        $("body").attr(JsSettingsTooltip.BODY_DATA_ATTRIBUTE_THEME, themeName);
        JsCookie.setJsSettingsSelectedTheme(themeName);

        $(`${JsSettingsTooltip.SETTINGS_THEME_COLOR_INPUT_SELECTOR}[value="${themeName}"]`)
            .attr("checked", "checked")
            .prop("checked", true);
    }


}