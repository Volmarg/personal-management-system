
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

}