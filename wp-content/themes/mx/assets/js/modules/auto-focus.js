/**
 * auto_focus
 * @version 1.0.3
 */
module.exports = function($fm,attr){
	if(!$fm) 
		return false;
	if(!attr)
		attr = '[required]';
	var focus = $fm.querySelector(attr);
	if(focus && focus.value === '')
		focus.focus();
};