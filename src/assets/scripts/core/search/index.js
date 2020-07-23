import * as $ from 'jquery';

// todo - remove? Not used with my logic?
//  or just move this to my logic

export default (function () {
  $('.search-toggle').on('click', e => {
    $('.search-box, .search-input').toggleClass('active');
    $('.search-input input').focus();
    e.preventDefault();
  });
}());
