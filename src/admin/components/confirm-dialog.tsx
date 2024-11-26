import { ReactElement } from 'react';
import { Button, ButtonGroup, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

interface ConfirmDialogParams {
	setIsConfirmDialogOpen: ( value: boolean ) => void;
	onConfirm: () => void;
	confirmButtonText?: string;
	title: string;
	children: any;
}

export const ConfirmDialog = ( props: ConfirmDialogParams ): ReactElement => {
	const {
		setIsConfirmDialogOpen,
		onConfirm,
		confirmButtonText,
		title,
		children,
	} = props;

	return (
		<Modal
			title={ title }
			size={ 'small' }
			isDismissible={ false }
			onRequestClose={ () => setIsConfirmDialogOpen( false ) }
		>
			<div
				style={ {
					maxWidth: '400px',
					marginBlockEnd: '30px',
					marginBlockStart: '0',
				} }
			>
				{ children }
			</div>
			<ButtonGroup
				style={ {
					display: 'flex',
					justifyContent: 'flex-end',
					gap: '30px',
				} }
			>
				<Button
					variant={ 'link' }
					onClick={ () => setIsConfirmDialogOpen( false ) }
				>
					{ __( 'Cancel', 'temporary-login' ) }
				</Button>
				<Button variant={ 'primary' } onClick={ onConfirm }>
					{ confirmButtonText || __( 'Confirm', 'temporary-login' ) }
				</Button>
			</ButtonGroup>
		</Modal>
	);
};
