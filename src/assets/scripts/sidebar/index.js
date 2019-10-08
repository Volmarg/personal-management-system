import * as $ from 'jquery';

export default (function () {

  if (typeof window.sidebar === 'undefined') {
    window.sidebar = {};
  }

  sidebar.links = {

    init: function() {
      // Sidebar links
      $('.sidebar .sidebar-menu li a').on('click', function () {
        const $this = $(this);

        if ($this.parent().hasClass('open')) {
          $this
              .parent()
              .children('.dropdown-menu')
              .slideUp(200, () => {
                $this.parent().removeClass('open');
              });
        } else {
          $this
              .parent()
              .parent()
              .children('li.open')
              .children('.dropdown-menu')
              .slideUp(200);

          $this
              .parent()
              .parent()
              .children('li.open')
              .children('a')
              .removeClass('open');

          $this
              .parent()
              .parent()
              .children('li.open')
              .removeClass('open');

          $this
              .parent()
              .children('.dropdown-menu')
              .slideDown(200, () => {
                $this.parent().addClass('open');
              });
        }
      });

      // Sidebar Activity Class
      const sidebarLinks = $('.sidebar').find('.sidebar-link');

      sidebarLinks
          .each((index, el) => {
            $(el).removeClass('active');
          })
          .filter(function () {
            const href = $(this).attr('href');
            const pattern = href[0] === '/' ? href.substr(1) : href;
            return pattern === (window.location.pathname).substr(1);
          })
          .addClass('active');

      // ٍSidebar Toggle
      $('.sidebar-toggle').on('click', e => {
        $('.app').toggleClass('is-collapsed');
        e.preventDefault();
      });

      /**
       * Wait untill sidebar fully toggled (animated in/out)
       * then trigger window resize event in order to recalculate
       * masonry layout widths and gutters.
       */
      $('#sidebar-toggle').click(e => {
        e.preventDefault();
        setTimeout(() => {
          window.dispatchEvent(window.EVENT);
        }, 300);
      });
    },
    markCurrentMenuElementAsActive: function(){
      // this is done directly by matching url in menu
      //let currUrl       = unescape(window.location.pathname);
      let currUrl       = window.location.pathname;
      let currMenuLink  = $('[href="' + currUrl + '"');

      let currActiveMenuLink = $('.sidebar-menu li.nav-item a.active');

      if( 0 === currMenuLink.length ){
        throw("Could not find menu link for currently visited page. (currUrl: " + currUrl + ")");
      }

      // first find curr active and deactivate it
      $(currActiveMenuLink).removeClass('active');

      // set current active
      $(currMenuLink).addClass('active');
    }
  };

  sidebar.links.init();

}());