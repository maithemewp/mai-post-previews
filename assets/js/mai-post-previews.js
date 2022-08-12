( async function() {
	var urls     = [];
	var previews = document.getElementsByClassName( 'mai-post-preview-ajax' );

	if ( ! previews.length ) {
		return;
	}

	Array.from( previews ).forEach( function( preview ) {
		var url = preview.getAttribute( 'data-url' );

		if ( url ) {
			urls.push( url );
		}
	});

	if ( ! urls.length ) {
		return;
	}

	fetch( maippScriptVars.root + 'maipostpreviews/v1/urls/', {
		method: 'PUT',
		credentials: 'same-origin',
		body: urls,
	})
	.then(function(response) {
		return response.json();
	})
	.then(function(data) {
		if ( data.success ) {
			Array.from( previews ).forEach( function( preview ) {
				var url = preview.getAttribute( 'data-url' );
				var div = document.createElement( 'div' );
				div.innerHTML = data.previews[url];
				var node = div.firstElementChild;
				preview.innerHTML = node.innerHTML;
				preview.classList.remove( 'mai-post-preview-ajax' );
			});
		}
	})
	.catch(function(error) {
		console.log( 'Mai Post Previews:', error );
	});
} )();
