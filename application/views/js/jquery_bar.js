(function($) {
	$.fn.bar = function(options) 
    {
		var opts = $.extend({}, $.fn.bar.defaults, options);
        var jbar = $('.jbar');
        if(!jbar.length)
        {
            jbar.remove();
        }

        if (opts.time)
            timeout = setTimeout('jQuery.fn.bar.removebar()',opts.time);
        var _message_span = $(document.createElement('span')).addClass('jbar-content').text(opts.message);
        _message_span.css({"color" : opts.color});
        var _wrap_bar;
        (opts.position == 'bottom') ? 
        _wrap_bar	  = $(document.createElement('div')).addClass('jbar jbar-bottom'):
        _wrap_bar	  = $(document.createElement('div')).addClass('jbar jbar-top') ;
        
        _wrap_bar.css({"background-color" 	: opts.background_color});
        if(opts.removebutton){
            var _remove_cross = $(document.createElement('a')).addClass('jbar-cross');
            _remove_cross.click(function(e){$.fn.bar.removebar();})
        }
        else{				
            _wrap_bar.css({"cursor"	: "pointer"});
            _wrap_bar.click(function(e){$.fn.bar.removebar();})
        }	
        _wrap_bar.append(_message_span).append(_remove_cross).hide().insertBefore($(opts.container)).fadeIn('fast');
	};
	var timeout;
	$.fn.bar.removebar 	= function(txt) {
		if($('.jbar').length){
			clearTimeout(timeout);
			$('.jbar').fadeOut('fast',function(){
				$(this).remove();
			});
		}	
	};
	$.fn.bar.defaults = {
		background_color 	: '#FFFFFF',
		color 				: '#000',
		position		 	: 'top',
		removebutton     	: true,
		time			 	: 5000,
        container           : '.content'
	};
	
})(jQuery);
