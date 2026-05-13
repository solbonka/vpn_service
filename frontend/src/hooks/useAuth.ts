import {useSelector} from 'react-redux';
import {IRootState} from '../store';

export const useAuth = () => {
    const authState = useSelector((state: IRootState) => state.auth.authData);
    return {
        isLoggedIn: !!authState.accessToken,
        isInitialized: authState.isInitialized
    };
};