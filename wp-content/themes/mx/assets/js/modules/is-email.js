/**
 * Check the value is email or not
 * 
 * 
 * @params string c the email address
 * @return bool true An email address if true
 * @version 1.0.1
 * 
 */
module.exports = function(e){
	var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return re.test(e);
};