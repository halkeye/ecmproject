jQuery(document).ready(function(){
    Scheduler.init();
});

var Scheduler = function() {
    var firstClick, $schedItemSelectors, $scheduleContent;
    function clearAllOnFirstClick(){
        firstClick = 0;
        for(var i = $schedItemSelectors.length; i--; ) {
            var className = $schedItemSelectors.eq(i).attr('class');
            $scheduleContent.find('.schedItem.' + className).addClass(className + 'Offline').removeClass(className);
        }
    } 
    return {
        init: function() {
            firstClick = 1;
            $schedItemSelectors = jQuery("#schedItemSelector").find("div");
            $scheduleContent = jQuery("#scheduleContent");
            
            //Set toggle
            $schedItemSelectors.toggle(function(){
                if (firstClick) {
                    clearAllOnFirstClick();
                }
                var obj = jQuery(this);
                obj.addClass('selectedSchedButton');
                var className = obj.attr('class').replace(' selectedSchedButton', '');
                $scheduleContent.find('.schedItem.' + className + 'Offline').removeClass(className + 'Offline').addClass(className);
            }, function(){
                var obj = jQuery(this);
                obj.removeClass('selectedSchedButton');
                var className = obj.attr('class').replace(' selectedSchedButton', '');
                $scheduleContent.find('.schedItem.' + className).addClass(className + 'Offline').removeClass(className);
            });
        }
    };
}();
