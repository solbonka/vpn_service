import {Button, Nav} from 'react-bootstrap'
import {useNavigate} from 'react-router-dom';
import {useAuth} from '../../hooks/useAuth';
import {useAppDispatch} from "../../store";
import {logoutUser} from "../../store/auth/actionCreators";


function User() {
    const navigate = useNavigate();
    const dispatch = useAppDispatch();

    const {isLoggedIn} = useAuth();

    return (
        <Nav.Item>
            {!isLoggedIn && (
                <Button
                    variant="outline-light"
                    style={{
                        padding: '8px 16px',
                        fontSize: '14px',
                        fontWeight: '400'
                    }}
                    onClick={() => navigate('/admin/login')}
                >
                    Войти
                </Button>
            )}

            {isLoggedIn && (
                <Button
                    variant="outline-light"
                    style={{
                        padding: '8px 16px',
                        fontSize: '14px',
                        fontWeight: '400'
                    }}
                    onClick={() => dispatch(logoutUser(navigate))}
                >
                    Выйти
                </Button>
            )}
        </Nav.Item>
    );
}

export default User;