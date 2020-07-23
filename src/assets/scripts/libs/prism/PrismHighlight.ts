import * as $ from 'jquery';

import Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

export default class PrismHighlight {

    /**
     * @type Object
     */
    private static attributes = {
        data:   {
            highlightPrismCode: "data-highlight-prism-code"
        },
    };

    /**
     * Main initialization logic
      */
    private init(): void
    {
        this.highlightCode();
    };

    /**
     * Calling prism on tinymce elements
     */
    private highlightCode(): void
    {
        let $codeElements               = $('code[class^="language-"]');
        let $tinyMceCodeElements        = $('#tiny-mce-wrapper code');
        let $prismHighlightedCodeElements = $('[' + PrismHighlight.attributes.data.highlightPrismCode + '=true] pre');

        let $allElements = $.merge($codeElements, $tinyMceCodeElements);
        $allElements     = $.merge($allElements, $prismHighlightedCodeElements);

        $.each($allElements, function(index, codeElement){
            Prism.highlightElement(codeElement);
        });
    }

}