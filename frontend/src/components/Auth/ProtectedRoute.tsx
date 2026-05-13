import React from 'react';
import {Navigate, useLocation} from 'react-router-dom';
import {useAuth} from "../../hooks/useAuth";

interface ProtectedRouteProps {
    children: React.ReactNode;
    redirectTo?: string;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({children, redirectTo = '/admin/login'}) => {
    const location = useLocation();
    const {isLoggedIn} = useAuth();

    if (!isLoggedIn) {
        return <Navigate to={redirectTo} state={{from: location}} replace/>;
    }

    return <>{children}</>;
};

export default ProtectedRoute;
