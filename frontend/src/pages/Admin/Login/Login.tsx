import React from 'react';
import {Tabs, Tab, Card, Form, Button, Alert} from "react-bootstrap";
import {useNavigate, useLocation} from 'react-router-dom';
import {useState, FormEvent, useEffect} from 'react';
import {IRootState, useAppDispatch} from "../../../store";
import {loginUser, clearValidationErrorAction} from "../../../store/auth/actionCreators";
import {useSelector} from "react-redux";

function Login() {
    const navigate = useNavigate();
    const location = useLocation();
    const [activeTab, setActiveTab] = useState('login');

    const isLoggedIn = useSelector((state: IRootState) => !!state.auth.authData.accessToken);
    const isLoading = useSelector((state: IRootState) => state.auth.authData.isLoading);
    const error = useSelector((state: IRootState) => state.auth.authData.error);
    const validationErrors = useSelector((state: IRootState) => state.auth.authData.validationErrors);

    const handleSelect = (key: string | null) => {
        if (key) {
            setActiveTab(key);
        }
    };

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    useEffect(() => {
        if (isLoggedIn && !isLoading) {
            const from = location.state?.from?.pathname || '/';
            navigate(from, {replace: true});
        }
    }, [isLoggedIn, isLoading, navigate, location.state]);

    const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setEmail(e.target.value);
        if (validationErrors?.email) {
            dispatch(clearValidationErrorAction('email'));
        }
    };

    const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setPassword(e.target.value);
        if (validationErrors?.password) {
            dispatch(clearValidationErrorAction('password'));
        }
    };

    const dispatch = useAppDispatch();
    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        dispatch(loginUser({
            email,
            password
        }))
    }

    const getFieldError = (fieldName: string): string | null => {
        return validationErrors?.[fieldName]?.[0] || null;
    };

    return (
        <>
            <title>Авторизация</title>

            <div className="login-page d-flex justify-content-center align-items-center" style={{ minHeight: 'calc(100vh - 58px)' }}>
                <Card
                    style={{
                        width: "450px",
                        maxWidth: "90vw",
                        maxHeight: "90vh",
                        background: "rgba(255, 255, 255, 0.25)",
                        backdropFilter: "blur(10px)",
                        borderRadius: "16px",
                        border: "1px solid rgba(255, 255, 255, 0.18)",
                        boxShadow: "0 8px 32px 0 rgba(31, 38, 135, 0.37)"
                }}
                >
                    <Card.Body>
                        <Tabs
                            id="user-login"
                            activeKey={activeTab}
                            onSelect={handleSelect}
                        >
                            <Tab className="text-center" eventKey="login" title="Войти">
                                <Form className="p-3" onSubmit={handleSubmit}>
                                    <Form.Group controlId="formEmail" className="mb-3">
                                        <Form.Control
                                            type="email"
                                            placeholder="E-mail"
                                            value={email}
                                            onChange={handleEmailChange}
                                            isInvalid={!!getFieldError('email')}
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {getFieldError('email')}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="formPassword" className="mb-4">
                                        <Form.Control
                                            type="password"
                                            placeholder="Пароль"
                                            value={password}
                                            onChange={handlePasswordChange}
                                            isInvalid={!!getFieldError('password')}
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {getFieldError('password')}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    {error && (
                                        <Alert variant="danger" className="mb-3">
                                            {error}
                                        </Alert>
                                    )}

                                    <Button
                                        variant="success"
                                        type="submit"
                                        className="w-100"
                                        disabled={isLoading}
                                    >
                                        {isLoading ? 'Вход...' : 'Войти'}
                                    </Button>
                                </Form>
                            </Tab>
                        </Tabs>
                    </Card.Body>
                </Card>
            </div>
        </>
    );
}

export default Login