import WidgetDataDto    from "../../../../DTO/WidgetDataDto";
import Env              from "../../../../env";
import ConsoleLogger    from "../../../ConsoleLogger";
import WidgetData       from "./WidgetData";

export default class WidgetDataLoader {

    public static getDataForWidgetId(widgetId: string): WidgetDataDto|null
    {

        switch(widgetId)
        {
            case WidgetData.widgetsIds.addContactCardWidget:
            {
                return WidgetData.addContactCardWidget();
            }

            case WidgetData.widgetsIds.addNote:
            {
                return WidgetData.addNoteWidget();
            }

            case WidgetData.widgetsIds.myFilesNewFolderWidget:
            {
                return WidgetData.myFilesNewFolder();
            }

            case WidgetData.widgetsIds.myImagesNewFolderWidget:
            {
                return WidgetData.myImagesNewFolder();
            }

            case WidgetData.widgetsIds.myVideoNewFolderWidget:
            {
                return WidgetData.myVideoNewFolder();
            }

            case WidgetData.widgetsIds.pendingIssuesCreateIssue:
            {
                return WidgetData.pendingIssuesCreateIssue();
            }

            case WidgetData.widgetsIds.filesUploadWidget:
            {
                return WidgetData.filesUpload();
            }

            default:
                if(Env.isDev()){
                    ConsoleLogger.info("There might be an ID which was not handled upon rewriting to TS", [
                        {
                            "widgetId" : widgetId
                        }
                    ])
                }
                return null;
        }
    }

}