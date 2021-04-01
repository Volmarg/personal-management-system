let autoComplete = require("@tarekraafat/autocomplete.js");
require("@tarekraafat/autocomplete.js/dist/css/autoComplete.css");
/**
 * @description handles autocompletion. This is required as the simplest solution `datalist` does not support events.
 *
 * @link https://www.npmjs.com/package/autocomplete.js?activeTab=versions
 * @link https://tarekraafat.github.io/autoComplete.js/#/
 */
export default class AutocompleteService {

    static readonly AUTOCOMPLETE_RESULT_ELEMENT_SELECTOR = "#autoCompleteResult";

    /**
     * @description will initialize the autoComplete
     *              keep in mind that the internal logic of this library initializes the logic for any element with
     *              id="autoComplete" - it was not investigated how to enforce custom selector
     */
    public static init(dataBag: Array<Object>, objectKeys: Array<string>, selectionCallback: Function, matchResultModificationCallback ?: Function): boolean
    {
       new autoComplete({
            data: {
                src   : dataBag,
                key   : objectKeys,
                cache : true,
            },
            observer: true,                      // Input field observer              | (Optional)
            debounce: 300,                       // Post duration for engine to start | (Optional)
            searchEngine: "strict",              // Search Engine type/mode           | (Optional)
            resultsList: {                       // Rendered results list object      | (Optional)
                container: source => {
                    source.setAttribute("id", "searchResultAutocomplete");
                },
                destination: AutocompleteService.AUTOCOMPLETE_RESULT_ELEMENT_SELECTOR,
                position: "afterend",
                element: "ul"
            },
            maxResults: 150,                         // Max. number of rendered results | (Optional)
            highlight: {
                render: true,                      // Highlight matching results      | (Optional)
            },
            resultItem: {                          // Rendered result item            | (Optional)
                content: (data, source) => {

                    if(null !== matchResultModificationCallback){
                        data = matchResultModificationCallback(data);
                    }

                    source.innerHTML = data.match;
                },
                element: "li"
            },
            onSelection: feedback => {             // Action script onSelection event | (Optional)
                let selectedValue = feedback.selection.value;
                selectionCallback(selectedValue);
            }
        });

       return true;
    }

}