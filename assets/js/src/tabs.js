/**
 * Handles tabbed interfaces within WoowBox:
 */

jQuery(function($) {

  // Define some general vars
  var woow_tabs_nav = '.woowbox-tabs-nav',     // Container of tab navigation items (typically an unordered list)
    woow_tabs_hash = window.location.hash,
    woow_tabs_current_tab = woow_tabs_hash.replace('!', '').split('/')[0];

  // Change tabs on click.
  // Tabs should be clickable elements, such as an anchor or label.
  $(woow_tabs_nav).on('click', '.nav-tab, a', function(e) {

    // Prevent the default action
    e.preventDefault();

    // Get the clicked element and the nav tabs
    var woow_tabs = $(this).closest(woow_tabs_nav),
      woow_tabs_section = $(woow_tabs).data('container'),
      woow_tab = ((typeof $(this).attr('href') !== 'undefined') ? $(this).attr('href') : $(this).data('tab'));

    // Don't do anything if we're clicking the already active tab.
    if($(this).hasClass('woowbox-active')) {
      return;
    }

    // Remove the active class from everything in this tab navigation and section
    $(woow_tabs).find('.woowbox-active').removeClass('woowbox-active');
    $(woow_tabs_section).find('div.woowbox-active').removeClass('woowbox-active');

    // Add the active class to the chosen tab and section
    $(this).addClass('woowbox-active');
    $(woow_tabs_section).find(woow_tab).addClass('woowbox-active');

    // Update the window URL to contain the selected tab as a hash in the URL.
    window.location.hash = woow_tab.split('#').join('#!');

  });

  // If the URL contains a hash beginning with woowbox-tab, mark that tab as open
  // and display that tab's panel.
  if(woow_tabs_hash && woow_tabs_hash.indexOf('woowbox-tab-') >= 0) {
    // Find the tab panel that the tab corresponds to
    var woow_tabs_section = $(woow_tabs_current_tab).parent(),
      woow_tab_nav = $(woow_tabs_section).data('navigation');

    // Remove the active class from everything in this tab navigation and section
    $(woow_tab_nav).find('.woowbox-active').removeClass('woowbox-active');
    $(woow_tabs_section).find('div.woowbox-active').removeClass('woowbox-active');

    // Add the active class to the chosen tab and section
    $(woow_tab_nav).find('a[href="' + woow_tabs_current_tab + '"]').addClass('woowbox-active');
    $(woow_tabs_current_tab).addClass('woowbox-active');
  }

});
