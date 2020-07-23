export default class Popover {
    /**
     * Will initialize popover by the data attr
     */
    public static init(){
        //@ts-ignore
        $('[data-toggle-popover="true"]').popover();
    };
}