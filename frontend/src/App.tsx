import Layout from './components/Layout'
import './App.css'
import AppRoutes from "./routes/routes";
import {useAppDispatch} from "./store";
import {useEffect} from "react";
import {initializeAuth} from "./store/auth/actionCreators";
import {useAuth} from "./hooks/useAuth";
import LoadingSpinner from "./components/UI/LoadingSpinner";

function App() {
    const dispatch = useAppDispatch();
    const { isInitialized } = useAuth();

    useEffect(() => {
        dispatch(initializeAuth());
    }, [dispatch]);

    if (!isInitialized) {
        return <LoadingSpinner fullscreen />;
    }

    return (
        <Layout>
            <AppRoutes/>
        </Layout>
    )
}

export default App
