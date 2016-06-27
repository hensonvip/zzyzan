module.exports = function(){    
	var args = arguments;  
	return arguments[0].replace(/\{(\d+)\}/g,                    
		function(m,i){
			return args[parseInt(i)+1];    
		});     
};
