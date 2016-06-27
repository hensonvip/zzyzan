/**
 * status_tip
 *
 * @param mixed
 * @return string
 * @version 1.1.3
 */
module.exports = function(){
	var defaults = ['type','size','content','wrapper'],
		types = ['loading','success','error','question','info','ban','warning'],
		sizes = ['small','middle','large'],
		wrappers = ['div','span'],
		type,
		icon,
		size,
		wrapper,
		content,	
		args = arguments;
	switch(args.length){
		case 0:
			return false;
		/** 
		 * only content
		 */
		case 1:
			content = args[0];
			break;
		/** 
		 * only type & content
		 */
		case 2:
			type = args[0];
			content = args[1];
			break;
		/** 
		 * other
		 */
		default:
			for(var i in args){
				eval(defaults[i] + ' = args[i];');
			}
	}
	if(!type)
		type = types[0];
	if(!size)
		size = sizes[0];
	if(!wrapper)
		wrapper = wrappers[0];

	switch(type){
		case 'success':
			icon = 'check-circle';
			break;
		case 'error' :
			icon = 'times-circle';
			break;
		case 'info':
		case 'warning':
			icon = 'exclamation-circle';
			break;
		case 'question':
		case 'help':
			icon = 'question-circle';
			break;
		case 'ban':
			icon = 'minus-circle';
			break;
		case 'loading':
		case 'spinner':
			icon = 'circle-o-notch fa-spin';
			break;
		default:
			icon = type;
	}

	return '<' + wrapper + ' class="tip-status tip-status-' + size + ' tip-status-' + type + '"><i class="fa fa-' + icon + ' fa-fw"></i> ' + content + '</' + wrapper + '>';
}