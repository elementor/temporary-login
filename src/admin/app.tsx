import { ReactElement } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AppContent } from './components/app-content';

const queryClient = new QueryClient();

export const App = (): ReactElement => {
	return (
		<QueryClientProvider client={ queryClient }>
			<AppContent />
		</QueryClientProvider>
	);
};
