/**
 * WOOWbox Editor
 */

(function($) {
  var Gallery = wp.media.view.Settings.Gallery;
  wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
    template: function(view) {
      return $('#tmpl-woowbox-gallery-settings').html()
        + '<div class="wp-gallery-default-settings">' + wp.media.template('gallery-settings')(view) + '</div>';
    },
    render: function() {
      var renderMethod = Gallery.prototype.render.apply(this, arguments);
      this.updateWoowBoxSettings();

      return renderMethod;
    },
    updateChanges: function() {
      Gallery.prototype.updateChanges.apply(this, arguments);
      this.updateWoowBoxSettings();
    },
    updateWoowBoxSettings: function() {
      var key = 'woowbox-skin',
        value = this.model.get(key) || this.$('[data-setting="' + key + '"]').val(),
        $settings = this.$('.wp-gallery-default-settings').find('[data-setting]');

      this.$('#woowbox-skin-config').attr('data-skin', value);
      if('' === value || 'default' === value) {
        this.model.unset(key, {silent: true});
      }

      if(value && 'none' !== value) {
        $settings.each(function() {
          if($.inArray($(this).attr('data-setting'), ['_orderbyRandom']) !== -1) {
            return;
          }
          $(this).closest('.setting').hide();
        });
      }
      else {
        this.$('.wp-gallery-default-settings .setting').show();
      }
    }

  });

  // modal content.
  var modalContent = wp.template('woowbox-modal');
  $(document).on('click', '#woowbox-skin-config', function(e) {
    e.preventDefault();

    var title = $(this).data('title');
    var modal = $(modalContent({title: title}));

    modal.find('.media-modal-close, .media-modal-backdrop').on('click', function() {
      modal.remove();
    });

    var iframe = $('<iframe>', {
      src: $(this).attr('data-src') + encodeURIComponent($(this).attr('data-skin'))
    });
    modal.find('.media-frame-content').html(iframe);
    $('body').append(modal);

  });

})(jQuery);