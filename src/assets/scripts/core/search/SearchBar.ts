import * as $ from "jquery";

export default class SearchBar {

    /**
     * Will bind click event on search bar - top navbar
     */
    public static init(): void
    {
        $('.search-toggle').on('click', e => {
            $('.search-box, .search-input').toggleClass('active');
            $('.search-input input').focus();
            e.preventDefault();
        });
    }
}