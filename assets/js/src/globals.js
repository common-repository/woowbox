/**
 * Set WoowBox global object
 */
window.wp = window.wp || {};
window.WoowBox = window.WoowBox || {l10n: {}};
window.WoowBox.data = window.WoowBox.data || {};

window.WoowBox.Hook = {
  hooks: {},

  register: function(hookName, action, callback, context) {
    var names = hookName.split(' '), curName;
    for(var i = 0; i < names.length; i++) {
      curName = names[i];
      if(!this.exists(curName)) {
        this.hooks[curName] = {};
      }
      this.hooks[curName][action] = {callback: callback, context: context};
    }
  },

  call: function(hookName, args, context) {
    if('undefined' !== typeof(this.hooks[hookName])) {
      for(var key in this.hooks[hookName]) {
        // skip loop if the property is from prototype
        if(!this.hooks[hookName].hasOwnProperty(key)) {
          continue;
        }

        var action = this.hooks[hookName][key];
        if(!context) {
          context = action.context;
        }
        if(false === action.callback.apply(context, args)) {
          break;
        }
      }
    }
  },

  exists: function(hookName) {
    return 'undefined' !== typeof(this.hooks[hookName]);
  },

  remove: function(hookName, action) {
    if(this.exists(hookName)) {
      delete this.hooks[hookName][action];
    }
  },
};

