import {Routes, Route} from 'react-router-dom';
import Login from "../pages/Admin/Login/Login";
import Main from "../pages/Admin/Main/Main";
import Server from "../pages/Admin/Server/Server";
import RemnawaveHosts from "../pages/Admin/Server/RemnawaveHosts";
import MiniappSettings from "../pages/Admin/MiniappSettings/MiniappSettings";
import ReferralSettings from "../pages/Admin/ReferralSettings/ReferralSettings";
import ReferralAnalytics from "../pages/Admin/ReferralAnalytics/ReferralAnalytics.jsx";
import PromoCodes from "../pages/Admin/PromoCodes/PromoCodes";
import {ProtectedRoute, PublicRoute} from "../components/Auth";
import PassiveUsers from "../pages/Admin/Users/PassiveUsers";
import ChatDetail from "../pages/Admin/Users/ChatDetail";
import Index from "../pages/Index/Index"

function AppRoutes() {
    return (
        <Routes>
            {/* Публичные роуты - доступны только неавторизованным */}
            <Route path="/admin/login" element={
                <PublicRoute>
                    <Login/>
                </PublicRoute>
            }/>

            {/* Публичные роуты - доступны всем */}
            <Route path="/" element={
                <Index/>
            }/>

            {/* Защищенные роуты - доступны только авторизованным */}
            <Route path="/admin/servers" element={
                <ProtectedRoute>
                    <Server/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/remnawave-hosts" element={
                <ProtectedRoute>
                    <RemnawaveHosts/>
                </ProtectedRoute>
            }/>

            <Route path="/home" element={
                <ProtectedRoute>
                    <Main/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/miniapp-settings" element={
                <ProtectedRoute>
                    <MiniappSettings/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/referral-settings" element={
                <ProtectedRoute>
                    <ReferralSettings/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/referral-analytics" element={
                <ProtectedRoute>
                    <ReferralAnalytics/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/promo-codes" element={
                <ProtectedRoute>
                    <PromoCodes/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/users/passive" element={
                <ProtectedRoute>
                    <PassiveUsers/>
                </ProtectedRoute>
            }/>

            <Route path="/admin/chats/:chatId" element={
                <ProtectedRoute>
                    <ChatDetail/>
                </ProtectedRoute>
            }/>
        </Routes>
    );
}

export default AppRoutes;