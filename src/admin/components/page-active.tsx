import { ReactElement } from 'react';
import {
	Button,
	ExternalLink,
	Flex,
	FlexItem,
	Icon,
	Notice,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { QUERY_KEY } from '../common/constants';
import { extendAccess, revokeTemporaryUsers } from '../api';
import { useEffect, useState } from '@wordpress/element';
import { ConfirmDialog } from './confirm-dialog';

interface IActiveData {
	status: 'active';
	login_url: string;
	expiration_human: string;
}

export const PageActive = ( props: IActiveData ): ReactElement => {
	const [ isRevokeConfirmDialogOpen, setIsRevokeConfirmDialogOpen ] =
		useState( false );
	const [ isExtendConfirmDialogOpen, setIsExtendConfirmDialogOpen ] =
		useState( false );
	const [ isCopySuccess, setIsCopySuccess ] = useState( false );

	const queryClient = useQueryClient();

	const onSuccess = () => {
		return queryClient.invalidateQueries( {
			queryKey: [ QUERY_KEY ],
		} );
	};

	const removeAccessMutation = useMutation( {
		mutationFn: revokeTemporaryUsers,
		onSuccess,
	} );

	const extendAccessMutation = useMutation( {
		mutationFn: extendAccess,
		onSuccess,
	} );

	useEffect( () => {
		if ( isCopySuccess ) {
			const timer = setTimeout( () => {
				setIsCopySuccess( false );
			}, 3000 );

			return () => clearTimeout( timer );
		}
	}, [ isCopySuccess ] );

	return (
		<>
			<div
				style={ {
					margin: '20px -15px',
				} }
			>
				<Notice isDismissible={ false } status="warning">
					<Icon icon="warning" />{ ' ' }
					<strong>{ __( 'Be careful!', 'temporary-login' ) }</strong>{ ' ' }
					{ __(
						"Don't share this link with any untrusted sources.",
						'temporary-login'
					) }{ ' ' }
					<ExternalLink href="https://go.elementor.com/temp-login-wp-dash-help-revoke-access/">
						{ __( 'Learn more', 'temporary-login' ) }
					</ExternalLink>
				</Notice>
			</div>

			<div
				style={ {
					backgroundColor: '#F7F7F7',
					padding: '30px',
					marginBlockEnd: '30px',
				} }
			>
				<TextControl
					value={ props.login_url }
					type="url"
					label={ __( 'Access URL', 'temporary-login' ) + ':' }
					readOnly={ true }
					onFocus={ ( event ) => event.target.select() }
					onChange={ () => {} }
				/>

				<Flex direction="row">
					<FlexItem>
						<Button
							variant="primary"
							size="small"
							onClick={ async () => {
								try {
									await navigator.clipboard.writeText(
										props.login_url
									);
									setIsCopySuccess( true );
								} catch ( error ) {
									console.error( error ); // eslint-disable-line no-console
								}
							} }
						>
							{ isCopySuccess
								? __( 'Copied!', 'temporary-login' )
								: __( 'Copy URL', 'temporary-login' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<strong>
							{ __( 'Expiration', 'temporary-login' ) + ':' }
						</strong>
						{ ' ' + props.expiration_human + ' ' }
						<Button
							variant="secondary"
							size="small"
							onClick={ () =>
								setIsExtendConfirmDialogOpen( true )
							}
							disabled={ extendAccessMutation.isPending }
							isBusy={ extendAccessMutation.isPending }
						>
							{ __( 'Extend Access', 'temporary-login' ) }
						</Button>
					</FlexItem>
				</Flex>
			</div>

			<Flex justify="space-between">
				<FlexItem>
					<Button
						variant="secondary"
						onClick={ () => setIsRevokeConfirmDialogOpen( true ) }
						disabled={ removeAccessMutation.isPending }
						isBusy={ removeAccessMutation.isPending }
						isDestructive={ true }
						style={ {
							marginInlineStart: '8px',
						} }
					>
						{ __( 'Revoke Access', 'temporary-login' ) }
					</Button>
					{ removeAccessMutation.isError && (
						<div>
							{ __( 'An error has occurred', 'temporary-login' ) +
								': ' +
								removeAccessMutation.error?.message }
						</div>
					) }
				</FlexItem>
			</Flex>

			{ isRevokeConfirmDialogOpen && (
				<ConfirmDialog
					title={ __( 'Revoke Access', 'temporary-login' ) }
					setIsConfirmDialogOpen={ setIsRevokeConfirmDialogOpen }
					onConfirm={ () => {
						removeAccessMutation.mutate();
						setIsRevokeConfirmDialogOpen( false );
					} }
					confirmButtonText={ __( 'Revoke', 'temporary-login' ) }
				>
					{ __(
						'Remove authorization for access to your website.',
						'temporary-login'
					) }
				</ConfirmDialog>
			) }

			{ isExtendConfirmDialogOpen && (
				<ConfirmDialog
					title={ __( 'Extend Access', 'temporary-login' ) }
					setIsConfirmDialogOpen={ setIsExtendConfirmDialogOpen }
					onConfirm={ () => {
						extendAccessMutation.mutate();
						setIsExtendConfirmDialogOpen( false );
					} }
					confirmButtonText={ __( 'Extend', 'temporary-login' ) }
				>
					{ __(
						'Extend the temporary access to your site.',
						'temporary-login'
					) }
				</ConfirmDialog>
			) }
		</>
	);
};
