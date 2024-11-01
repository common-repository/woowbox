/**
 * WOOWbox Settings
 */

/**
 * You'll need to use CodeKit or similar, as this file is a placeholder to combine
 * the following JS files into __FILE__.min.js:
 */
// @codekit-prepend "src/globals.js";
// @codekit-prepend "src/tabs.js";

(function($) {

  Vue.use(Toasted);

  var woow = window.WoowBox,
    $activity = $('#activity');

  var abstractField,
    components = {};
  abstractField = {
    props: [
      'skin',
      'preset',
      'schema',
      'id',
      'disabled',
      'options'
    ],
    data: function() {
      return {};
    },

    computed: {
      value: {
        get: function() {
          var val = null;
          if(!this.skin || !this.preset) {
            return;
          }
          if(typeof this.$root.model !== 'undefined' && this.id) {
            val = this.$root.model[this.id];
          }
          if(val === null) {
            val = this.schema.default;
          }
          return this.formatValueToField(val);
        },

        set: function(newValue) {
          if(!this.skin || !this.preset) {
            return;
          }
          newValue = this.formatValueToModel(newValue);
          if(this.id) {
            this.$root.model[this.id] = newValue;
            // this.$emit('model-updated', newValue, this.id);
          }
        }
      }
    },

    methods: {
      getFieldName: function() {
        var nameTemplate = this.options && this.options.fieldNameTemplate ? this.options.fieldNameTemplate : '{name}',
          name;
        if(this.schema.attr && this.schema.attr.name) {
          name = this.schema.attr.name;
        }
        name = name || this.schema.name || this.id;
        name = nameTemplate.replace('{name}', name);

        return name;
      },

      getFieldType: function(isInput) {
        var type;
        if(this.schema.attr && this.schema.attr.type) {
          type = this.schema.attr.type;
        }
        type = type || this.schema.type || 'text';

        if(isInput && 'color' === type) {
          return 'text';
        }
        return type;
      },

      getFieldID: function() {
        var prefix = this.options && this.options.fieldIdPrefix ? this.options.fieldIdPrefix : '',
          id;
        if(this.schema.attr && this.schema.attr.id) {
          id = this.schema.attr.id;
        }
        id = id || this.schema.id || this.id;

        return prefix + id;
      },

      getFieldClasses: function() {
        var classes = ['field-' + this.id];
        if(this.schema.attr && this.schema.attr.class) {
          if(_.isArray(this.schema.attr.class)) {
            _.union(classes, this.schema.attr.class);
          }
          else {
            classes.push(this.schema.attr.class);
          }
        }
        return classes;
      },

      getFieldAttributes: function() {
        var attrs = {},
          fieldType = this.getFieldType();
        if(fieldType && 'text' !== fieldType) {
          attrs['data-type'] = fieldType;
        }
        if(this.schema.attr) {
          attrs = $.extend(attrs, this.schema.attr);
        }
        if(this.schema.props) {
          var i = 0,
            length = this.schema.props.length,
            key;
          for(; i < length; i++) {
            key = this.schema.props[i];
            attrs[key] = true;
          }
        }
        attrs['data-preset'] = this.preset;
        return attrs;
      },

      formatValueToField: function(value) {
        return value;
      },

      formatValueToModel: function(value) {
        return value;
      }
    }
  };
  components['field-input'] = Vue.component('field-input',
    {
      mixins: [abstractField],
      template: '<div class="wrapper">\n' +
        '    <input class="form-control"\n' +
        '           :class="getFieldClasses()"\n' +
        '           :id="getFieldID()"\n' +
        '           :type="getFieldType(true)"\n' +
        '           :name="getFieldName()"\n' +
        '           v-model="value"\n' +
        '           v-bind="getFieldAttributes()"\n' +
        '    >\n' +
        '    <span class="helper" v-if="getFieldType() === \'color\' || getFieldType() === \'range\'">{{ value }}</span>\n' +
        '</div>\n',
      data: function() {
        return {
          picker: null
        };
      },
      watch: {
        disabled: function(val) {
          if('color' === this.getFieldType() && $.fn.spectrum) {
            if(val) {
              this.picker.spectrum('disable');
            }
            else {
              this.picker.spectrum('enable');
            }
          }
        },
        value: function(val) {
          if('color' === this.getFieldType() && $.fn.spectrum && this.picker) {
            this.picker.spectrum('set', val);
          }
        }
      },
      mounted: function() {
        this.$nextTick(function() {
          if('color' === this.getFieldType()) {
            if($.fn.spectrum) {
              this.spectrumInit();
            }
            else {
              console.warn('Spectrum color library is missing.');
            }
          }
        });
      },
      beforeDestroy: function() {
        if(this.picker) {
          this.picker.spectrum('destroy');
        }
      },
      methods: {
        spectrumInit: function() {
          var vm = this;
          this.picker = $('input[data-type="color"]', this.$el).spectrum('destroy').spectrum(
            _.defaults(
              this.schema.options || {},
              {
                color: this.value,
                showInput: true,
                showAlpha: true,
                disabled: this.disabled,
                allowEmpty: false,
                preferredFormat: 'hex',
                change: function(color) {
                  vm.value = color ? color.toString() : null;
                },
                move: function(color) {
                  vm.value = color ? color.toString() : null;
                }
              }
            )
          );
          // this.picker.spectrum('set', this.value);
        },
        formatValueToModel: function(value) {
          if(value !== null) {
            var type = this.getFieldType();
            switch(type) {
              case 'number':
                return Number(value);
              case 'range':
                return Number(value);
            }
          }

          return value;
        }
      }
    });
  components['field-checkbox'] = Vue.component('field-checkbox',
    {
      mixins: [abstractField],
      template: '<div class="wrapper">\n' +
        '    <input type="checkbox"\n' +
        '           v-model="value"\n' +
        '           :class="getFieldClasses()"\n' +
        '           :id="getFieldID()"\n' +
        '           :name="getFieldName()"\n' +
        '           v-bind="getFieldAttributes()"\n' +
        '    >\n' +
        '</div>',
      methods: {
        formatValueToField: function(value) {
          return !!value;
        },

        formatValueToModel: function(value) {
          if(value !== null) {
            return Number(value);
          }

          return value;
        }
      }
    });
  components['field-select'] = Vue.component('field-select',
    {
      mixins: [abstractField],
      template: '<div class="wrapper">\n' +
        '    <select class="form-control"\n' +
        '           v-model="value"\n' +
        '           :class="getFieldClasses()"\n' +
        '           :id="getFieldID()"\n' +
        '           :name="getFieldName()"\n' +
        '           v-bind="getFieldAttributes()"\n' +
        '    >\n' +
        '         <option v-for="item in items" :value="getItemValue(item)" :disabled="isItemDisabled(item)">{{ getItemName(item) }}</option>\n' +
        '    </select>\n' +
        '</div>',
      data: function() {
        return {
          items: this.schema.options
        };
      },
      methods: {
        getItemValue: function(item) {
          if(_.isObject(item)) {
            if(typeof item['value'] !== 'undefined') {
              return item.value;
            }
            else {
              throw '`value` is not defined';
            }
          }
          else {
            return item;
          }
        },
        getItemName: function(item) {
          if(_.isObject(item)) {
            if(typeof item['name'] !== 'undefined') {
              return item.name;
            }
            else {
              throw '`name` is not defined';
            }
          }
          else {
            return item;
          }
        },
        isItemDisabled: function(item) {
          if(_.isObject(item)) {
            if(typeof item['premium'] !== 'undefined') {
              return item.premium && !config.premium;
            }
            else {
              return false;
            }
          }
          else {
            return false;
          }
        }
      }
    });

  var tick;
  var config = new Vue({
    el: '#woowbox',
    components: components,
    data: {
      options: {
        fieldNameTemplate: '_woow_skin[{name}]'
      },
      premium: false,
      // chosen skin (slug)
      skin: '',
      default_skin: '',
      // chosen skin preset
      preset: 'default',
      presets: ['default'],
      skin_info: '',
      // skin settings
      model: {},
      // skin default settings
      defaults: {},
      // skin schema
      schema: {},
      activeTab: '',
      new_preset: false,
      new_preset_name: '',
      activity: false
    },
    computed: {
      isSettingsDefault: function() {
        return (JSON.stringify(this.model) === JSON.stringify(this.defaults));
      },
      isSettingsChanged: function() {
        var activity = this.activity,
          model1 = JSON.stringify(this.model),
          model2 = JSON.stringify($.extend({}, this.defaults, window.woow_skin[this.skin]['model'][this.preset]));
        return model1 !== model2;
      }
    },
    watch: {
      skin: function(skin) {
        this.schema = $.extend({}, window.woow_skin[skin]['schema']);
        this.skin_info = $.extend({}, window.woow_skin[skin]['info']);
        this.activeTab = _.keys(this.schema)[0];
        this.defaults = setDefaults(this.schema);

        this.updatePresets(skin);
        if(-1 === this.presets.indexOf(this.preset)) {
          this.preset = 'default';
        }

        this.model = $.extend({}, this.defaults, window.woow_skin[skin]['model'][this.preset]);

        this.fakeActivity(400);

        function setDefaults(obj, def_obj) {
          def_obj = def_obj || {};
          $.each(obj, function(key, val) {
            if(typeof val !== 'object') {
              return;
            }
            if(typeof val['default'] !== 'undefined') {
              def_obj[key] = val['default'];
            }
            else {
              setDefaults(val, def_obj);
            }
          });
          return def_obj;
        }
      },
      preset: function(preset) {
        this.model = $.extend({}, this.defaults, window.woow_skin[this.skin]['model'][preset]);
        this.fakeActivity(400);
      },
      new_preset_name: function(new_preset_name) {
        this.new_preset_name = new_preset_name.replace(/[&\/\\#,+()\[\]~%.'":;?<>{}^=|`]/g, '');
      }
    },
    mounted: function() {
      // On init get gallery skin and set all the data
      this.premium = !!$('#woowbox-license').val();
      this.default_skin = $('#woowbox-default-skin').val();
      var skin = this.default_skin.split(': ');
      this.skin = skin[0];
      if(skin[1]) {
        this.preset = skin[1];
      }
      else {
        this.preset = 'default';
      }
    },
    methods: {
      fakeActivity: function(time) {
        this.activity = true;
        if(tick) {
          clearTimeout(tick);
        }
        tick = setTimeout(function() {
          config.activity = false;
          tick = null;
        }, time);
      },
      switchTab: function(tab_id) {
        this.activeTab = tab_id;
      },
      // Get style classes of field
      getFieldRowClasses: function(field) {
        var baseClasses = {
          disabled: this.fieldDisabled(field),
          readonly: this.fieldReadonly(field),
          required: this.fieldRequired(field),
          'premium-field': this.fieldPremium(field)
        };

        if(_.isArray(field.styleClasses)) {
          _.each(field.styleClasses, function(c) {
            baseClasses[c] = true;
          });
        }
        else if(_.isString(field.styleClasses)) {
          baseClasses[field.styleClasses] = true;
        }

        baseClasses['field-' + field.tag] = true;

        return baseClasses;
      },

      // Should field type have a label?
      fieldTypeHasLabel: function(field) {
        var relevantType = field.type || field.tag;
        if(field.attr && field.attr.type) {
          relevantType = field.attr.type;
        }
        switch(relevantType) {
          case 'button':
          case 'submit':
          case 'reset':
            return false;
          default:
            return true;
        }
      },

      // Get disabled attr of field
      fieldDisabled: function(field) {
        if(!field.prop || !field.prop.disabled) {
          return false;
        }

        return field.prop.disabled;
      },

      // Get required prop of field
      fieldRequired: function(field) {
        if(!field.prop || !field.prop.required) {
          return false;
        }

        return field.prop.required;
      },

      // Get premium prop of field
      fieldPremium: function(field) {
        return !!field.premium;
      },

      // Get visible prop of field
      fieldVisible: function(field) {
        if(!field.visible) {
          return true;
        }

        var filter,
          visible;
        try {
          filter = compileExpression(field.visible);
          visible = filter(this.model);
        } catch(e) {
          visible = true;
        }

        return visible;
      },

      // Get readonly prop of field
      fieldReadonly: function(field) {
        if(!field.prop || !field.prop.readonly) {
          return false;
        }

        return field.prop.readonly;
      },

      // Get current hint.
      fieldHint: function(field) {
        return field.hint;
      },

      // Get type of field 'field-xxx'. It'll be the name of HTML element
      getFieldTagType: function(field) {
        return 'field-' + field.tag;
      },

      // modelUpdated: function(newVal, key){
      // },

      updatePresets: function(skin) {
        this.presets = Object.keys(window.woow_skin[skin]['model']);
        this.new_preset = false;
        this.new_preset_name = '';
      },

      // save skin data via AJAX
      saveSkinSettings: function() {
        this.activity = true;

        var skin = this.skin,
          preset = this.new_preset ? this.new_preset_name : this.preset,
          model = this.model,
          defaults = this.defaults,
          data = {
            action: 'woow_save_skin_data',
            _nonce_woow_skin_settings_save: $('#_nonce_woow_skin_settings_save').val(),
            skin: skin,
            preset: preset,
            data: JSON.stringify(model)
          };
        if('default' === preset && this.isSettingsDefault) {
          data.default_reset = true;
        }

        if(!preset) {
          this.$toasted.error(woow.l10n.fill_preset_name, {duration: 2000});
          this.activity = false;
          $('#woowskin_preset').focus();
          return;
        }

        // Post updated gallery data.
        $.post(
          ajaxurl,
          data,
          function(response) {
            // Response should be a JSON success with the message
            if(response && response.success) {
              window.woow_skin[skin]['model'][preset] = $.extend({}, defaults, model);
              config.updatePresets(skin);
              config.skin = skin;
              config.preset = preset;
              // Display some success message
              config.$toasted.success(response.data, {duration: 2000});

              config.updateSkinsListSetting();
            }
            else if(response && response.data) {
              // Display some error here
              config.$toasted.error(response.data, {duration: 2000});
            }
            else {
              config.$toasted.error(':(', {duration: 2000});
            }
          },
          'json'
        ).always(function() {
          config.activity = false;
        });

      },

      // delete skin preset
      deletePreset: function() {
        this.activity = true;

        var skin = this.skin,
          preset = this.preset,
          data = {
            action: 'woow_delete_skin_preset',
            _nonce_woow_skin_settings_save: $('#_nonce_woow_skin_settings_save').val(),
            skin: skin,
            preset: preset
          };

        var default_skin = this.default_skin.split(': ');
        if(default_skin[1] === preset || 'default' === preset) {
          this.$toasted.error(woow.l10n.delete_default_preset_error, {duration: 2000});
          this.activity = false;
          return;
        }

        // Post updated gallery data.
        $.post(
          ajaxurl,
          data,
          function(response) {
            // Response should be a JSON success with the message
            if(response && response.success) {
              delete window.woow_skin[skin]['model'][preset];
              config.updatePresets(skin);
              config.skin = skin;
              config.preset = 'default';
              // Display some success message
              config.$toasted.success(response.data, {duration: 2000});

              config.updateSkinsListSetting();
            }
            else if(response && response.data) {
              // Display some error here
              config.$toasted.error(response.data, {duration: 2000});
            }
            else {
              config.$toasted.error(':(', {duration: 2000});
            }
          },
          'json'
        ).always(function() {
          config.activity = false;
        });

      },

      // reset skin settings changes
      resetSkinSettingsChanges: function() {
        this.model = $.extend({}, this.defaults, window.woow_skin[this.skin]['model']);
      },

      // reset skin settings to default
      resetSkinSettings: function() {
        this.model = $.extend({}, this.defaults);
      },

      // reset skin settings to default
      updateSkinsListSetting: function() {
        var options = '';
        $.each(window.woow_skin, function(skin, data) {
          options += '<option value="' + skin + '">' + data.info.name + '</option>';
          $.each(data.model, function(presetName, presetData) {
            if('default' === presetName) {
              return;
            }
            options += '<option value="' + skin + ': ' + presetName + '">' + data.info.name + ': ' + presetName + '</option>';
          });
        });

        // WoowBox Settings page.
        var default_skin = $('select#woowbox-default-skin');
        if(default_skin.length) {
          default_skin.find('option[value=""]').nextAll().remove();
          default_skin.append(options);
          default_skin.val(this.default_skin);
        }

        var select, select_val,
          global_default_value, global_default_name;
        // Add Media modal.
        if(window.parent !== window && typeof window.parent.jQuery === 'function') {
          select = window.parent.jQuery('select#woowbox-skin');
          if(select.length) {
            global_default_value = select.attr('data-default_skin');
            global_default_name = select.find('option[value="' + global_default_value + '"]').text();

            select_val = select.val();
            select.find('option[value="none"]').nextAll().remove();
            select.append(options);
            select.find('option[value="' + global_default_value + '"]').text(global_default_name);
            select.val(select_val);

            var tmpl = window.parent.jQuery('#tmpl-woowbox-gallery-settings');
            var tmpl_html = $(tmpl.html());
            var tmpl_select = tmpl_html.find('select#woowbox-skin');
            tmpl_select.find('option[value="none"]').nextAll().remove();
            tmpl_select.append(options);
            tmpl_select.find('option[value="' + global_default_value + '"]').text(global_default_name);
            tmpl.html(tmpl_html);

            return;
          }
        }

        // Elementor.
        if(window.parent !== window && typeof window.parent.elementor === 'object') {
          var configOptions = {};
          if(woow.l10n.default_skin) {
            configOptions.default = woow.l10n.txt_default;
          }
          $.each(window.woow_skin, function(skin, data) {
            configOptions[skin] = data.info.name;
            $.each(data.model, function(presetName, presetData) {
              if('default' === presetName) {
                return;
              }
              configOptions[skin + ': ' + presetName] = data.info.name + ': ' + presetName;
            });
          });
          if(woow.l10n.default_skin) {
            configOptions[woow.l10n.default_skin] += woow.l10n.txt_default_skin_sign;
          }
          window.parent.ElementorConfig.widgets['woowbox-gallery'].controls.skin.options = configOptions;
          // window.parent.elementor.reloadPreview();

          select = window.parent.jQuery('.elementor-control-skin select');
          if(select.length) {
            select_val = select.val();
            select.find('option').remove().append(options);
            select.append(options);
            if(woow.l10n.default_skin) {
              select.find('option[value="' + woow.l10n.default_skin + '"]').append(woow.l10n.txt_default_skin_sign);
            }
            select.val(select_val);
          }

          window.parent.jQuery('.elementor-control-change_skin_settings_trigger').find('input').val(+new Date()).trigger('input');
        }
      }

    }
  });

  woow.Skin = config;

  $(function() {
    var $license_field = $('#woowbox-license'),
      $license_button = $('.woowbox-license-action-button'),
      $license_plugin = $('#woowbox-license-plugin');
    $license_button.on('click', function(e) {
      e.preventDefault();

      var license = $license_field.val(),
        action = $(this).attr('data-action');

      if(license && action) {
        woow_check_license(license, action);
      }
    });

    if('WoowBox' === $license_plugin.val()) {
      woow_check_license($license_field.val(), 'check');
    }

    function woow_check_license(license, action) {
      if('check' === action && !license) {
        return;
      }

      $license_button.parent().addClass('activity');

      // Send the ajax request to validate the license.
      $.post(
        'https://woowgallery.com/woowbox/license-server.php',
        {
          action: action,
          site: woow.l10n.siteurl,
          key: license
        },
        function(response) {
          var key = '';
          // Response should be a JSON success with the gallery data
          if(response && response.success) {
            key = response.key;
            if(response.success.message) {
              Vue.toasted.success(response.success.message, {duration: 2000});
            }
          }
          else if(response && response.error) {
            Vue.toasted.error(response.error.message, {duration: 2000});
          }
          else {
            Vue.toasted.error(':(', {duration: 2000});
            if('check' === action) {
              return;
            }
          }
          if('activate' === action && !key) {
            return;
          }
          if('check' === action && key) {
            return;
          }
          $.post(
            ajaxurl,
            {
              action: 'woowbox_license',
              _nonce_woowbox_settings_save: $('#_nonce_woowbox_settings_save').val(),
              license: key,
              license_action: action
            },
            function(response) {
              // Response should be a JSON success with the message
              if(response && response.success) {
                if(response.data) {
                  Vue.toasted.success(response.data, {duration: 2000});
                }
                setTimeout(function() {
                  window.location.href = window.location.href;
                }, 1600);
              }
              else if(response && response.data) {
                if(response.data) {
                  // Display some error here
                  Vue.toasted.error(response.data, {duration: 2000});
                }
              }
              else if(key) {
                Vue.toasted.error(':@', {duration: 2000});
              }
            },
            'json'
          );
        },
        'json'
      ).always(function() {
        $license_button.parent().removeClass('activity');
      });
    }
  });

})(jQuery);