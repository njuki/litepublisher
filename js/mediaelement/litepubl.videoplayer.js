;(function( $){
$.fn.videoplayer= function(opt) {
return $(this).mediaelementplayer($.extend(opt ? opt : {}, 
{
pluginPath: ltoptions.files + "/js/mediaelement/",
        features: ['playpause','progress','current','volume']
},
ltoptions.lang != "ru" ? {} : {
		playpauseText: 'Воспроизвести/Пауза',
				stopText: 'Остановить',
		muteText: 'Отключение звука',
				fullscreenText: 'Развернуть',
						tracksText: 'Субтитры',
								postrollCloseText: 'Закрыть'
}));
};
})( jQuery);