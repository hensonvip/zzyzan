/** 
 * cookie
 */
module.exports = {
	/**
	 * get_cookie
	 * 
	 * @params string
	 * @return string
	 * @version 1.0.0
	 */
	get : function(c_name){
		var i,x,y,ARRcookies=document.cookie.split(';');
		for(i=0;i<ARRcookies.length;i++){
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf('='));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf('=')+1);
			x=x.replace(/^\s+|\s+$/g,'');
			if(x==c_name) return unescape(y);
		}
	},
	/**
	 * set_cookie
	 * 
	 * @params string cookie key name
	 * @params string cookie value
	 * @params int the expires days
	 * @return n/a
	 * @version 1.0.0
	 */
	set : function(c_name,value,exdays){
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? '' : '; expires=' + exdate.toUTCString());
		document.cookie = c_name + '=' + c_value;
	}
};