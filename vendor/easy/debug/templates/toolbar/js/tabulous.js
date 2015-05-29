$(document).ready(function($) {
    $('#debug-toolbar').tabulous();
});

;(function ( $, window, document, undefined ) {

    var pluginName = "tabulous",
        defaults = {};

    function Plugin( element, options ) {
        this.element = element;
        this.$elem = $(this.element);
        this.options = $.extend( {}, defaults, options );
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype = {

        init: function() {
            
            var links = this.$elem.find('li a');
            this.$elem.find('li:last-child').after('<span class="debug-toolbar-clear"></span>');
            
            var firstdiv = this.$elem.find('#debug-toolbar-container');
            
            var alldivs = this.$elem.find('#debug-toolbar-container div');
            var res = this.$elem.find('#n-element');

            links.bind('click', {myOptions: this.options}, function(e) {
                e.preventDefault();
                
                var mythis = $(this);
                var thislink = mythis.attr('href');
                
                if(thislink === '#close'){
                    firstdiv.css({'display': 'none'});
                    res.css({'display': 'none'});
                    links.removeClass('active');
                    alldivs.removeClass('active');
                }else{
                    firstdiv.css({'display': 'block'});
                    res.css({'display': 'block'});
                    links.removeClass('active');
                    mythis.addClass('active');
                    alldivs.removeClass('active');
                    firstdiv.find('div'+thislink).addClass('active');
                }
            });
        }
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            new Plugin( this, options );
        });
    };

})( jQuery, window, document );


$(function() {
    $("#debug-toolbar-container").resizable({handles: {n: $("#n-element")}, minHeight: "200", maxHeight: "500"}).bind("resize", function (e, ui) {
            $(this).css("top", "auto");
    });
});