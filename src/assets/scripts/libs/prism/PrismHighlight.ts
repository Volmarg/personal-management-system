import * as $ from 'jquery';

import * as Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating.js';
import 'prismjs/components/prism-php.js';
import 'prismjs/components/prism-javascript.js';
import 'prismjs/components/prism-markup.js';
import 'prismjs/components/prism-css.js';
import 'prismjs/components/prism-bash.js';
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
     * @description Main initialization logic
      */
    public init(): void
    {
        this.highlightCode();
    };

    /**
     * @description Calling prism on tinymce elements
     */
    public highlightCode(): void
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