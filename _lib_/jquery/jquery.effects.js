/**
$.fn.highlight = function(){
  this.css("background", "#ffff99")
  var self = this;
  setTimeout(function(){
    self.css("background", "inherit");
  }, 500);
};
**/

/***/
$.fn.highlight = function () {  //http://stackoverflow.com/questions/848797/yellow-fade-effect-with-jquery
    $(this).each(function () {
        var el = $(this);
        $("<div/>")
        .width(el.outerWidth())
        .height(el.outerHeight())
        .css({
            "position": "absolute",
            "left": el.offset().left,
            "top": el.offset().top,
            "background-color": "#ffff99",
            "opacity": ".95",
            "z-index": "9999999"
        }).appendTo('body').fadeOut(1000).queue(function () { $(this).remove(); });
    });
}
$.fn.highlightSlow = function () {  //http://stackoverflow.com/questions/848797/yellow-fade-effect-with-jquery
    $(this).each(function () {
        var el = $(this);
        $("<div/>")
        .width(el.outerWidth())
        .height(el.outerHeight())
        .css({
            "position": "absolute",
            "left": el.offset().left,
            "top": el.offset().top,
            "background-color": "#fde8f9",
            "opacity": ".40",
            "z-index": "9999999"
        }).appendTo('body').fadeOut(2000).queue(function () { $(this).remove(); });
    });
}
/**/