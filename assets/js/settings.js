( function () {
	var select = document.getElementById( 'ai_provider_github_models_default_text_model' );

	if ( ! select ) {
		return;
	}

	var data = window.aiProviderGithubModels || {};

	function showFallback() {
		select.innerHTML = '';

		var emptyOption = document.createElement( 'option' );
		emptyOption.value = '';
		emptyOption.textContent = data.noOverrideLabel;
		select.appendChild( emptyOption );

		select.disabled = false;
	}

	fetch( data.ajaxUrl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams( {
			action: 'ai_provider_github_models_get_models',
			nonce: data.nonce,
		} ),
	} )
		.then( function ( response ) {
			return response.json();
		} )
		.then( function ( json ) {
			if ( ! json.success ) {
				showFallback();
				return;
			}

			select.innerHTML = '';

			var emptyOption = document.createElement( 'option' );
			emptyOption.value = '';
			emptyOption.textContent = data.noOverrideLabel;
			select.appendChild( emptyOption );

			json.data.forEach( function ( modelId ) {
				var option = document.createElement( 'option' );
				option.value = modelId;
				option.textContent = modelId;
				if ( modelId === data.currentValue ) {
					option.selected = true;
				}
				select.appendChild( option );
			} );

			select.disabled = false;
		} )
		.catch( showFallback );
} )();
