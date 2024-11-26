import { ReactElement } from 'react';
import { Button, CheckboxControl, Notice } from '@wordpress/components';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { QUERY_KEY } from '../common/constants';
import { __, sprintf } from '@wordpress/i18n';
import { generateTemporaryUser } from '../api';
import { ConfirmDialog } from './confirm-dialog';
import { useState } from '@wordpress/element';

interface IInActiveData {
	status: 'inactive';
	current_user_logged_in_display_name: string;
}

export const PageInactive = (): ReactElement => {
	const [ isConfirmDialogOpen, setIsConfirmDialogOpen ] = useState( false );
	const [ isKeepUserPosts, setIsKeepUserPosts ] = useState( false );

	const queryClient = useQueryClient();

	const data = queryClient.getQueryData< IInActiveData >( [ QUERY_KEY ] );

	const generateUserMutation = useMutation( {
		mutationFn: () => {
			setIsConfirmDialogOpen( false );

			return generateTemporaryUser( isKeepUserPosts );
		},
		onSuccess: () => {
			return queryClient.invalidateQueries( {
				queryKey: [ QUERY_KEY ],
			} );
		},
	} );

	return (
		<>
			<Notice isDismissible={ false } status="info">
				<ContentAttributedExplain
					displayName={ data.current_user_logged_in_display_name }
				/>
				<div>
					<CheckboxControl
						__nextHasNoMarginBottom
						label={ __(
							'Save content after access expires',
							'temporary-login'
						) }
						checked={ isKeepUserPosts }
						onChange={ setIsKeepUserPosts }
					/>
				</div>
			</Notice>

			<Button
				variant="primary"
				onClick={ () => setIsConfirmDialogOpen( true ) }
				disabled={ generateUserMutation.isPending }
				isBusy={ generateUserMutation.isPending }
				style={ { marginTop: '20px' } }
			>
				{ __( 'Grant Access', 'temporary-login' ) }
			</Button>
			{ generateUserMutation.isError && (
				<div>
					{ __( 'An error has occurred', 'temporary-login' ) +
						': ' +
						generateUserMutation.error?.message }
				</div>
			) }

			{ isConfirmDialogOpen && (
				<ConfirmDialog
					title={ __( 'Grant Access', 'elementor' ) }
					setIsConfirmDialogOpen={ setIsConfirmDialogOpen }
					onConfirm={ generateUserMutation.mutate }
					confirmButtonText={ __( 'Grant', 'temporary-login' ) }
				>
					<p>
						{ __(
							'Authorize Temporary Login to create admin-level access to your website.',
							'temporary-login'
						) }
					</p>
				</ConfirmDialog>
			) }
		</>
	);
};

const ContentAttributedExplain = ( { displayName } ) => {
	const formattedMessage = sprintf(
		/* translators: %s: user display name */
		__(
			'Content created by the temporary login user is temporary. To save it, select the option provided. The content will then be attributed to the %s user once the temporary access expires.',
			'temporary-login'
		),
		'<strong>' + displayName + '</strong>'
	);

	return (
		<p
			dangerouslySetInnerHTML={ {
				__html: formattedMessage,
			} }
		/>
	);
};
