import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

import { App } from './app';

domReady( (): void => {
	const htmlOutput = document.getElementById( 'temporary-login-admin' );

	if ( htmlOutput ) {
		render( <App />, htmlOutput );
	}
} );
