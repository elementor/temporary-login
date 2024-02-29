import { ReactElement } from 'react';
import { Button } from '@wordpress/components';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { QUERY_KEY } from '../common/constants';
import { __ } from '@wordpress/i18n';
import { generateTemporaryUser } from '../api';
import { ConfirmDialog } from './confirm-dialog';
import { useState } from '@wordpress/element';

interface IInActiveData {
	status: 'inactive';
}

export const PageInactive = (): ReactElement => {
	const [ isConfirmDialogOpen, setIsConfirmDialogOpen ] = useState( false );

	const queryClient = useQueryClient();

	const data = queryClient.getQueryData< IInActiveData >( [ QUERY_KEY ] );

	const generateUserMutation = useMutation( {
		mutationFn: () => {
			setIsConfirmDialogOpen( false );

			return generateTemporaryUser();
		},
		onSuccess: () => {
			return queryClient.invalidateQueries( {
				queryKey: [ QUERY_KEY ],
			} );
		},
	} );

	return (
		<>
			<Button
				variant="primary"
				onClick={ () => setIsConfirmDialogOpen( true ) }
				disabled={ generateUserMutation.isPending }
				isBusy={ generateUserMutation.isPending }
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
					{ __(
						'Authorize Temporary Login to create admin-level access to your website.',
						'temporary-login'
					) }
				</ConfirmDialog>
			) }
		</>
	);
};
