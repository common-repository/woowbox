(function($) {
  $(function() {
    elementor.channels.data.on('element:after:remove', function(model) {
      if (window.elementorFrontend && window.elementorFrontend.elements.window.WoowBox) {
        window.elementorFrontend.elements.window.WoowBox.junkRemove();
      }
    });

    var modalContent = wp.template('woowbox-modal');
    elementor.channels.editor.on('woowbox:module:settings', function(event) {
      var modal = $(modalContent({title: window.WoowBoxElementor.modal_title})),
        skin = event.$el.closest('#elementor-controls').find('.elementor-control-skin select').val(),
        src = window.WoowBoxElementor.modal_src.replace('skin=default', 'skin=' + encodeURIComponent(skin));

      modal.find('.media-modal-close, .media-modal-backdrop').on('click', function() {
        modal.remove();
      });

      var iframe = $('<iframe>', {
        src: src
      });
      modal.find('.media-frame-content').html(iframe);
      $('body').append(modal);
    });

  });
})(jQuery);
