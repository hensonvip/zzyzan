(function ( $ ) {
  $.fn.colorizer = function(options) {
    options = $.extend({
      colors: [[0, 0, 0], [225, 225, 225]],
      cssrule: 'background-color',
      currentColor: [0,0,0],
      step: 10,
      target: this
    }, options);

    var lastScrollTop = 0;
    options.target.css(options.cssrule, 'rgb('+options.colors[0][0]+','+options.colors[0][1]+','+options.colors[0][2]+')');
    
    $(window).on('scroll', function(event) { 
      var st = $(this).scrollTop();
      if (st != lastScrollTop){
        colorize(options, st);
      }
      lastScrollTop = st;
    });

    var colorize = function(options, st) {
      for (var i=0;i<3;i++) {
        if (options.colors[0][i] < options.colors[1][i]) {
          options.currentColor[i] = options.colors[0][i] + Math.floor(st/options.step);
          if (options.currentColor[i] > options.colors[1][i]) {
            options.currentColor[i] = options.colors[1][i];
          }
        } else if ((options.colors[0][i] > options.colors[1][i])) {
          options.currentColor[i] = options.colors[0][i] - Math.floor(st/options.step);
          if (options.currentColor[i] < options.colors[1][i]) {
            options.currentColor[i] = options.colors[1][i];
          }
        }
      }
      options.target.css(options.cssrule, 'rgb('+options.currentColor[0]+','+options.currentColor[1]+','+options.currentColor[2]+')');
    };
  }
}(jQuery));

