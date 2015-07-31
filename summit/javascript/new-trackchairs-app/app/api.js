
/*
* The track chairs API listeners and triggers to fetch server-side data
*/

// Requirements and globals
reqwest = require('reqwest')
var api = riot.observable()
var url = 'http://openstack.dev/trackchairs/api/v1/'

/*
*	Listeners
*/

api.on('load-summit-details', function(id){

	var append = 'summit/'
	id = typeof id !== 'undefined' ? id : 'active'
	var append = append + id

	reqwest({
	    url: url + append
	  , method: 'get'
	  , success: function (resp) {
			api.trigger('summit-details-loaded', resp)
	    }
	})	

})


// Request to track chair selections for a particular category
api.on('load-selections', function(categoryId){

	console.log('4a. api hears load selections.');

	reqwest({
	    url: url + 'selections/' + categoryId + '/'
	  , method: 'get'
	  , success: function (resp) {
	  		console.log('response from server loading selctions: ', resp)
			console.log('4b. api fires selections loaded.');	  		
			api.trigger('selections-loaded', resp)
	    }
	})
})


// Request to load presenations
api.on('load-presentations', function(query,categoryId){

	var append = '?'
	if(query) { append = append + 'keyword=' + encodeURI(query) }
	if(categoryId) { append = append + '&category=' + encodeURI(categoryId) }

	reqwest({
	    url: url + append
	  , method: 'get'
	  , success: function (resp) {
			api.trigger('presentations-loaded', resp.results)
	    }
	})

})

// Request to pull details for a particular presenation
api.on('load-presentation-details', function(id){

	reqwest({
	    url: url + 'presentation/' + id + '/'
	  , method: 'get'
	  , success: function (resp) {
			api.trigger('presentation-details-loaded', resp)
	    }
	})
})

// Add a comment to the current presentation
api.on('add-comment', function(id, comment){

	reqwest({
	    url: url + 'presentation/' + id + '/comment'
	  , method: 'post'
	  , data: { comment: comment }
	  , success: function (resp) {
			api.trigger('comment-added', resp)
	    }
	})


})

// Add a comment to the current presentation
api.on('select-presentation', function(id){

	console.log('2a. API heard select presentation ' + id);

	reqwest({
	    url: url + 'presentation/' + id + '/select'
	  , method: 'get'
	  , success: function (resp) {
	  		console.log('2b. API is firing presentation-selected');
			api.trigger('presentation-selected', resp)
	    }
	})

})

api.on('save-sort-order', function(list_id, sort_order){


	reqwest({
	    url: url + 'reorder/'
	  , method: 'post'
	  , data: {sort_order: sort_order, list_id: list_id}
	  , success: function (resp) {
	  		api.trigger('sort-order-saved', resp)
	    }
	})	

})


module.exports = api;