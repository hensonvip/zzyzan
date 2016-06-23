var path = require('path');
module.exports = {
	root: path.join(__dirname, './../../'),
	resolve: {
		alias: {
			modules: path.join(__dirname, './../js/modules'),
			addons: path.join(__dirname, './../../addons')
		}
	},
	entry: {
		"frontend-entry": './frontend-entry.js',
		"frontend-logged": './frontend-logged.js',
		"backend-entry": './backend-entry.js',
		"backend-post-new-entry": './backend-post-new-entry.js'
	},
	output: {
		path: path.join(__dirname, './../js/'),
		filename: '[name].js',
		publicPath: "/assets/"
	}
}