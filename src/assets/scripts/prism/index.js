import * as $ from 'jquery';

import Prism from 'prismjs';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-bash';
import 'prismjs/themes/prism.css';

export default (function () {
    window.prismjs = {};
    prismjs = {
        init: function(){
            this.highlightCode();
        },
        highlightCode: function(){
            let $codeElements        = $('code[class^="language-"]');
            let $tinyMceCodeElements = $('#tiny-mce-wrapper code');
            let $allElements         = $.merge($codeElements, $tinyMceCodeElements);

            $.each($allElements, function(index, codeElement){
                Prism.highlightElement(codeElement);
            });
        }
    };
}())


