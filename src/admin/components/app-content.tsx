import { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { getAppData } from '../api';
import { PageInactive } from './page-inactive';
import { QUERY_KEY } from '../common/constants';
import { PageActive } from './page-active';
import { Panel, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const AppContent = (): ReactElement => {
	const { isPending, error, data } = useQuery( {
		queryKey: [ QUERY_KEY ],
		queryFn: getAppData,
	} );

	if ( isPending ) {
		return <div>{ __( 'Loading', 'temporary-login' ) }...</div>;
	}

	if ( error ) {
		return (
			<div>
				{ __( 'An error has occurred', 'temporary-login' ) }
				{ ': ' }
				{ error.message }
			</div>
		);
	}

	return (
		<div
			style={ {
				maxWidth: '1000px',
			} }
		>
			<Panel>
				<PanelBody>
					<h2>{ __( 'Temporary Login', 'temporary-login' ) }</h2>

					<p
						style={ {
							marginBlock: '30px',
						} }
					>
						{ __(
							"Temporary Login creates a secure, temporary URL for easy access to your WP admin with no username and password. Share this URL with trusted support agents and colleagues in order to resolve issues quickly, and shut down access as soon as you're done.",
							'temporary-login'
						) }
					</p>

					{ data.status === 'active' ? (
						<PageActive { ...data } />
					) : (
						<PageInactive />
					) }
				</PanelBody>
			</Panel>
		</div>
	);
};
