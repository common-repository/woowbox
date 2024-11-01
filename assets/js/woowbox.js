/**
 * WoowBox function to load required module assets.
 * @param options
 */
function WoowBoxC(data) {
  if(data) {
    for(var property in data) {
      this[property] = data[property];
    }
  }
  if(!this.galleries) {
    this.galleries = {};
  }
  this.skins_to_init = {};
  this.initListners();
}

WoowBoxC.prototype = {
  initListners: function() {
    // For Ajax Themes.
    var self = this;
    // Address bar.
    window.addEventListener('hashchange', function() {
      //self.trace(event);
      self.junkRemove();
    });
    window.addEventListener('popstate', function() {
      //self.trace(event);
      self.junkRemove();
    });

    // Track History - push.
    var realPushState = history.pushState;
    history.pushState = function(some, args, I, dunno) {
      //self.trace('push')
      self.junkRemove();
      return realPushState.apply(history, arguments); // leave this line exactly as-is.
    };

    // Track XMLHttpRequest.
    var oldXHR = window.XMLHttpRequest;

    function newXHR() {
      var realXHR = new oldXHR();
      realXHR.addEventListener('readystatechange', function() {
        if(realXHR.readyState == 4 && realXHR.status == 200) {
          self.junkRemove();
        }
      }, false);

      return realXHR;
    }

    window.XMLHttpRequest = newXHR;
  },
  /**
   * Remove useless listeners and objects.
   */
  junkRemove: function() {
    for(var gall_id in this.galleries) {
      if(!document.getElementById(gall_id) && this.galleries[gall_id]) {
        this.galleries[gall_id].remove();
        delete this.galleries[gall_id];
      }
    }
  },
  woowboxRequiredAssets: function(options) {
    var self = this;
    var links = options.links,
      request_type = options.request_type,
      init_skin = options.callback,
      gallery_id = options.gallery_id;

    if(!this.skins_to_init[init_skin]) {
      // Add first gallery to queue for a particular script.
      this.skins_to_init[init_skin] = [];
      this.skins_to_init[init_skin].push(gallery_id);
      // Start load skins and CSS
      document.addEventListener('readystatechange', loadRequiredAssets);
      loadRequiredAssets();
    }
    else {
      // Add other galleries of a certain type (skin).
      this.skins_to_init[init_skin].push(gallery_id);
      // Try init gallery.
      initGalleries();
    }

    /**
     * Add required assets on document ready state.
     */
    function loadRequiredAssets() {
      if('complete' !== document.readyState) {
        return;
      }
      var loadComplete = initGalleries;

      if(typeof window[init_skin] !== 'function') {
        var i = links.length - 1,
            loadNext, link;
        for (i; i >= 0; i--) {
          link = links[i];
          loadNext = loadComplete;
          loadComplete = self.loadJSCSS.bind(this, link, loadNext);
        }
      }
      // Execute the nested callbacks.
      loadComplete();
    }

    function initGalleries() {
      WoowBox.junkRemove();
      // Check skin is loaded.
      if(typeof window[init_skin] === 'function') {
        // Skin loaded
        document.removeEventListener('readystatechange', loadRequiredAssets);
        self.skins_to_init[init_skin].forEach(function(id) {
          window[init_skin](id);
        });
        // Clear queue for initialization.
        self.skins_to_init[init_skin].length = 0;

      }
    }
  },

  /**
   * JS and CSS file loader.
   * @param link
   * @param callback
   */
  loadJSCSS: function(link, callback) {
    var ext = link.split('?').shift().split('.').pop(),
      fileref, domref;

    if('js' === ext) {
      // JS <script> tag.
      domref = document.querySelector('script[src^="' + link + '"]');
      if(domref == null) {
        fileref = document.createElement('script');
        fileref.type = 'text/javascript';
        fileref.src = link;
      }
    }
    else if('css' === ext) {
      // CSS <link> tag.
      domref = document.querySelector('link[href^="' + link + '"]');
      if(domref == null) {
        fileref = document.createElement('link');
        fileref.rel = 'stylesheet';
        fileref.type = 'text/css';
        fileref.href = link;
      }
    }
    if(typeof fileref !== 'undefined') {
      if(callback) {
        fileref.onload = callback;
      }
      // Push it to the header.
      document.head.appendChild(fileref);
    }
    else if(callback) {
      callback();
    }
  },
  trace: function(data) {
    if(arguments.length == 1) {
      console.log(data);
      return;
    }
    var newString = '';
    for(var i = 0; i < arguments.length; i++) {
      newString += arguments[i] + ', ';
    }
    newString = newString.slice(0, -2);
    console.log(newString);
  }
};
var WoowBox = window.WoowBox ? new WoowBoxC(window.WoowBox) : new WoowBoxC();
