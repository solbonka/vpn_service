import React from 'react';
import {Navigate, useLocation} from 'react-router-dom';
import {useAuth} from "../../hooks/useAuth";

interface PublicRouteProps {
    children: React.ReactNode;
    redirectTo?: string;
}

const PublicRoute: React.FC<PublicRouteProps> = ({children, redirectTo = '/'}) => {
    const location = useLocation();
    const {isLoggedIn} = useAuth();

    if (isLoggedIn) {
        const from = location.state?.from?.pathname || redirectTo;
        return <Navigate to={from} replace/>;
    }

    return <>{children}</>;
};

export default PublicRoute;
