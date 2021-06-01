
export default class Application {

    /**
     * Will cancel execution of current code and will throw exception
     *
     * @param additionalMessage
     */
    public static abort(additionalMessage: string = "")
    {
        throw {
            "message"           : "Action was aborted",
            "additionalMessage" : additionalMessage
        }
    }

    /**
     * @description will set page title in <HEAD> tag
     *
     * @param title
     */
    public static setTitle(title: string): void
    {
        let $titleTag = $('title');
        $titleTag.text("PMS - " + title);
    }

}