(function ($) {
    $.fn.basketFloat = function () {
        $me = this;
        var position = this.position();
        var yPos;
        $me.wrap('<div id="fixedFloatWrapper" style="position:absolute;left:' + position.left + 'px;" />');

        $(window).scroll(function () {
            yPos = $(window).scrollTop();
            if (yPos >= position.top) {
                $me.css('position', 'fixed').css('top', '0');
            } else {
                $me.removeAttr("style");
            }
        });

        $(window).resize(function () {
            $('#fixedFloatWrapper').css('position', 'static');
            $me.removeAttr("style");
            position = $me.position();
            $('#fixedFloatWrapper').css({ 'position': 'absolute', 'left': position.left + 'px' });
            if (yPos >= position.top) {
                $me.css('position', 'fixed').css('top', '0');
            } else {
                $me.removeAttr("style");
            }
        });

    };
})(jQuery);