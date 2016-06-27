module.exports = function(obj){
	return Object.keys(obj).map(function(key){ 
		return encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]); 
	}).join('&');
};