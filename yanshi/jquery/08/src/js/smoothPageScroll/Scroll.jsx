const $STAGE = $('html,body');
export const scrollStop = () => $STAGE.queue([]).stop();

export class Scroll {
	constructor({
			easing = 'easeOutQuart',
			speed = 1000,
			delay = 0,
			isAddHash = true,
			isTopScroll = true,
			isLeftScroll = true
		}) {
			this.easing = easing;
			this.speed = speed;
			this.delay = delay;
			this.isAddHash = isAddHash;
			this.isTopScroll = isTopScroll;
			this.isLeftScroll = isLeftScroll;
	}

	getScrollPosition(target) {
		const isPositionHash = typeof target === 'string' && isFinite(parseInt(target.slice(1,2)));
		const position = isPositionHash ? target.slice(1).split(',') : $(target).offset();

		return {
			scrollTop: !target ? 0 :
				isPositionHash ? parseInt(position[1]) : position.top,
			scrollLeft: !target ? 0 :
				isPositionHash ? parseInt(position[0]) : position.left
		};
	}

	//Last resort for the difference of each browser
	getWindowSize() {
		const element = document.createElement('p');
		const body = $("body")[0];
		element.style.backgroundColor = 'fixed';
		element.style.width = element.style.height = '100%';
		body.appendChild(element);
		const {clientWidth, clientHeight} = element;
		body.removeChild(element);
		return {width: clientWidth, height: clientHeight}
	}

	getScrollFixPosition(scrollTop, scrollLeft) {
		const windowSize = this.getWindowSize() ;

		const maxScrollTop = $(document).height() - windowSize.height;
		if(scrollTop > maxScrollTop) scrollTop = Math.max(maxScrollTop, 0);

		const maxScrollLeft = $(document).width() - windowSize.width;
		if(scrollLeft > maxScrollLeft) scrollLeft = Math.max(maxScrollLeft, 0);

		if(this.isTopScroll && this.isLeftScroll) return {scrollTop, scrollLeft};
		else if(this.isTopScroll) return {scrollTop};
		else if(this.isLeftScroll) return {scrollLeft};
	}

	scrollStart(target, complate) {
		scrollStop();

		const {
			scrollTop,
			scrollLeft
		} = this.getScrollPosition(target);

		let isComplate = false;
		$STAGE.delay(this.delay).animate(
			this.getScrollFixPosition(scrollTop, scrollLeft),
			this.speed,
			this.easing,
			() => {
				if(isComplate) return;
				isComplate = true;
				if(this.isAddHash &&
					typeof target === 'string') {
					location.hash = target;
				}
				if(complate) complate({target, scrollTop, scrollLeft});
			}
		);
	}
}