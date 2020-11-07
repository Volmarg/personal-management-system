import videojs from 'video.js';
import 'video.js/dist/video-js.min.css'

/**
 * @description This script was prepared to work with videos
 * @bug         the player itself is initialized properly but non of the options is working when initialized in here
 */
export default class VideoJs {

    /**
     * @type Object
     */
    static readonly selectors = {
        videoDomElement: "video"
    };

    /**
     * @description Will initialize main logic for VideoJs
     */
    public init(): void
    {
        // at first it's required to invalidate all instances
        let allInstances = videojs.getAllPlayers();
        $.each(allInstances, (index, instance) => {
            instance.dispose();
        });

        // now get new state of video player and apply videojs
        let $allVideoElements = $(VideoJs.selectors.videoDomElement);
        $.each($allVideoElements, (index, element) => {
            let $element  = $(element);
            let elementId = $element.attr('id');
            videojs(elementId, {});
        })

    }

}