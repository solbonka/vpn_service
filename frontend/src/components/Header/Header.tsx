import {Navbar, Nav} from 'react-bootstrap'
import User from "./User";
import {useLocation, Link} from 'react-router-dom';
import {useAuth} from '../../hooks/useAuth';

function Header() {
    const location = useLocation();

    const {isLoggedIn} = useAuth();

    const appName = import.meta.env.VITE_APP_NAME;

    return (
        <Navbar
            bg="dark"
            variant="dark"
            expand="lg"
            fixed="top"
            style={{
                padding: "4px 24px"
            }}
        >
            <Navbar.Brand href="/">
                {appName}
            </Navbar.Brand>
            <Navbar.Toggle aria-controls="basic-navbar-nav"/>
            <Navbar.Collapse id="basic-navbar-nav" role="navigation">
                <Nav className="me-auto">
                    <Nav.Link
                        as={Link} to="/home"
                        active={location.pathname === '/home'}
                    >
                        Главная
                    </Nav.Link>
                    {isLoggedIn && (
                        <>
                            <Nav.Link
                                as={Link} to="/admin/servers"
                                active={location.pathname === '/admin/servers'}
                            >
                                Серверы
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/remnawave-hosts"
                                active={location.pathname === '/admin/remnawave-hosts'}
                            >
                                Хосты Remnawave
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/miniapp-settings"
                                active={location.pathname === '/admin/miniapp-settings'}
                            >
                                Настройки мини-приложения
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/referral-settings"
                                active={location.pathname === '/admin/referral-settings'}
                            >
                                Реферальная программа
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/referral-analytics"
                                active={location.pathname === '/admin/referral-analytics'}
                            >
                                Аналитика рефералов
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/promo-codes"
                                active={location.pathname === '/admin/promo-codes'}
                            >
                                Промокоды
                            </Nav.Link>
                            <Nav.Link
                                as={Link} to="/admin/users/passive"
                                active={location.pathname.startsWith('/admin/users')}
                            >
                                Пользователи
                            </Nav.Link>
                        </>
                    )}
                </Nav>

                <Nav>
                    <User/>
                </Nav>
            </Navbar.Collapse>
        </Navbar>
    )
}

export default Header