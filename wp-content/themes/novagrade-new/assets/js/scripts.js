jQuery(document).foundation();
/* 
These functions make sure WordPress 
and Foundation play nice together.
*/

var lastScroll = 50;

jQuery(document).ready(function() {
    jQuery('.accordion p:empty, .orbit p:empty').remove();

    $(window).scroll(function(){
        var scroll = $(window).scrollTop();
        if ($(window).scrollTop() + $(window).height() == $(document).height()) {
            $("#footer").addClass("active");
        } else if (scroll < lastScroll) {
            $("#footer").removeClass("active");
        }
        lastScroll = scroll;
    });
});