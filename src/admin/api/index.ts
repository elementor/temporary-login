interface WindowWithData extends Window {
	ePremiumSupportSettings: {
		nonce: string;
		ajaxurl: string;
	};
}

export const getAppData = async () => {
	const configData = getConfigData();

	const response = await fetch( configData.ajaxurl, {
		method: 'POST',
		headers: new Headers( {
			'Content-Type': 'application/x-www-form-urlencoded',
		} ),
		body: `action=temporary_login_get_app_data&nonce=${ configData.nonce }`,
	} );

	const responseJson = await response.json();

	if ( responseJson.success ) {
		return responseJson.data;
	}

	throw responseJson.data;
};

const getConfigData = () => {
	return ( window as unknown as WindowWithData ).ePremiumSupportSettings;
};

export const generateTemporaryUser = async () => {
	const configData = getConfigData();

	const response = await fetch( configData.ajaxurl, {
		method: 'POST',
		headers: new Headers( {
			'Content-Type': 'application/x-www-form-urlencoded',
		} ),
		body: `action=temporary_login_generate_temporary_user&nonce=${ configData.nonce }`,
	} );

	const responseJson = await response.json();

	if ( responseJson.success ) {
		return responseJson.data;
	}

	throw responseJson.data[ 0 ];
};

export const revokeTemporaryUsers = async () => {
	const configData = getConfigData();

	const response = await fetch( configData.ajaxurl, {
		method: 'POST',
		headers: new Headers( {
			'Content-Type': 'application/x-www-form-urlencoded',
		} ),
		body: `action=temporary_login_revoke_temporary_users&nonce=${ configData.nonce }`,
	} );

	const responseJson = await response.json();

	if ( responseJson.success ) {
		return responseJson.data;
	}

	throw responseJson.data[ 0 ];
};

export const extendAccess = async () => {
	const configData = getConfigData();

	const response = await fetch( configData.ajaxurl, {
		method: 'POST',
		headers: new Headers( {
			'Content-Type': 'application/x-www-form-urlencoded',
		} ),
		body: `action=temporary_login_extend_access&nonce=${ configData.nonce }`,
	} );

	const responseJson = await response.json();

	if ( responseJson.success ) {
		return responseJson.data;
	}

	throw responseJson.data[ 0 ];
};
