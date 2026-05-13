import axios, {AxiosError} from 'axios'
import {store} from '../store'
import {getAccessToken, logoutUser} from '../store/auth/actionCreators'
import Endpoints from './endpoints'
import { useNavigate } from 'react-router-dom'

export const axiosInstance = axios.create({
    baseURL: '/api',
    withCredentials: true
})

const urlsSkipAuth = [Endpoints.AUTH.LOGIN, Endpoints.AUTH.REFRESH]

axiosInstance.interceptors.request.use(async (config) => {
    if (config.url && urlsSkipAuth.includes(config.url)) {
        return config
    }

    const accessToken = await store.dispatch(getAccessToken())

    if (accessToken) {
        config.headers = config.headers || {};
        config.headers['Authorization'] = `Bearer ${accessToken}`;
    }

    return config
})

axiosInstance.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        const isLoggedIn = !!store.getState().auth.authData.accessToken

        if ((error.response?.status === 401) && isLoggedIn && error.request.url !== Endpoints.AUTH.LOGOUT) {

            const navigate = useNavigate()

            store.dispatch(logoutUser(navigate))
        }

        throw error
    }
)