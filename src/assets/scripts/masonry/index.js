import * as $ from 'jquery';
import Masonry from 'masonry-layout';

export default (function () {

  if (typeof window.ui === 'undefined') {
    window.ui = {};
  }

  ui.masonry = {
    init: function(){

      let masonryTargets = $('.masonry');
      if( 0 === masonryTargets.length ){
        return;
      }

      new Masonry('.masonry', {
        itemSelector: '.masonry-item',
        columnWidth: '.masonry-sizer',
        percentPosition: true,
      });
    }
  };

  window.addEventListener('load', () => {
    if ($('.masonry').length > 0) {
      ui.masonry.init();
    }
  });

}());
