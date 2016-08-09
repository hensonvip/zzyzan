import {Scroll, scrollStop} from './smoothPageScroll/Scroll';

$.smoothPageScrollByLoaded = function(options = {}) {
	const scrollreset = new Scroll({speed: 0});
	scrollreset.scrollStart();

	const scroll = new Scroll(options);
	scroll.scrollStart(location.hash, options.complate);

	const isWebKit = navigator.userAgent.indexOf('WebKit') !== -1;
	if (isWebKit) location.hash = '';
}

$.smoothPageScrollStart = function(options = {}) {
	const scroll = new Scroll(options);
	scroll.scrollStart(options.target, options.complate);
}

$.fn.smoothPageScroll = function(options = {}) {
	const scroll = new Scroll(options);

	$(document).on('click', $(this).selector, (e) => {
		const target = e.target.hash || options.target;
		scroll.scrollStart(target, options.complate);
		return false;
	});
};

global.addEventListener('DOMMouseScroll', scrollStop, false);
global.onmousewheel = document.onmousewheel = scrollStop;
$(document).on('click', () => scrollStop());