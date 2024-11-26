import { ReactElement } from 'react';
import {
	Button,
	ExternalLink,
	Flex,
	FlexItem,
	Icon,
	Notice,
	TextControl, Tooltip,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { QUERY_KEY } from '../common/constants';
import { extendAccess, revokeTemporaryUsers, sendToElementor } from '../api';
import { useEffect, useState } from '@wordpress/element';
import { ConfirmDialog } from './confirm-dialog';

interface IActiveData {
	status: 'active';
	login_url: string;
	expiration_human: string;
	is_elementor_connected: boolean;
	reassign_to: string;
	reassign_user_profile_link: string;
}

export const PageActive = ( props: IActiveData ): ReactElement => {
	const [ isRevokeConfirmDialogOpen, setIsRevokeConfirmDialogOpen ] =
		useState( false );
	const [ isExtendConfirmDialogOpen, setIsExtendConfirmDialogOpen ] =
		useState( false );
	const [ isSendToElementorDialogOpen, setIsSendToElementorDialogOpen ] =
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

	const sendToElementorMutation = useMutation( {
		mutationFn: sendToElementor,
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
					__nextHasNoMarginBottom
					value={ props.login_url }
					type="url"
					label={ __( 'Access URL', 'temporary-login' ) + ':' }
					readOnly={ true }
					onFocus={ ( event ) => event.target.select() }
					onChange={ () => {} }
					style={ {
						marginBottom: 'calc(8px)',
					} }
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
						{ props.reassign_to && (
							<>
								<strong>{ __( 'Content Attributed', 'temporary-login' ) }:</strong> <a href={ props.reassign_user_profile_link }>{ props.reassign_to }</a>
								{ '. ' }
							</>
						) }

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
				{ props.is_elementor_connected && (
					<FlexItem>
						<Button
							variant="link"
							onClick={ async () => {
								setIsSendToElementorDialogOpen( true );
							} }
							disabled={ sendToElementorMutation.isPending }
							isBusy={ sendToElementorMutation.isPending }
						>
							{ __(
								'Share with Elementor Support',
								'temporary-login'
							) }
						</Button>
						{ sendToElementorMutation.isError && (
							<div>
								{ __(
									'An error has occurred',
									'temporary-login'
								) +
									': ' +
									sendToElementorMutation.error?.message }
							</div>
						) }
					</FlexItem>
				) }
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

			{ isSendToElementorDialogOpen && (
				<ConfirmDialog
					title={ __(
						'Share with Elementor Support',
						'temporary-login'
					) }
					setIsConfirmDialogOpen={ setIsSendToElementorDialogOpen }
					onConfirm={ () => {
						sendToElementorMutation.mutate();
						setIsSendToElementorDialogOpen( false );
					} }
					confirmButtonText={ __( 'Share', 'temporary-login' ) }
				>
					<p>
						{ __(
							'Share temporary access with Elementor support.',
							'temporary-login'
						) }
					</p>
					<p>
						{ __(
							'You can revoke the access at any time with the revoke button.',
							'temporary-login'
						) }
					</p>
				</ConfirmDialog>
			) }
		</>
	);
};
