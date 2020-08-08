
export default class Navigation {

    public static getCurrentUri(): string
    {
        //@ts-ignore
        return window.TWIG_REQUEST_URI;
    }

    public static getCurrentGetAttrs(): string
    {
        //@ts-ignore
        return window.TWIG_GET_ATTRS;
    }

    public static getCurrentRoute(): string
    {
        //@ts-ignore
        return window.TWIG_ROUTE;
    }

}