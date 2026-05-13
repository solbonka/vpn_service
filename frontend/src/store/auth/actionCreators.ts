import {Dispatch} from "@reduxjs/toolkit"
import api from "../../api"
import {ILoginRequest, ILoginResponse, IAuthError} from "../../api/auth/types"
import {
    loginStart,
    loginSuccess,
    loginFailure,
    logoutSuccess,
    initAuthStart,
    initAuthSuccess,
    initAuthFailure,
    clearValidationError
} from "./authReducer"
import {AxiosPromise} from "axios"
import {store} from ".."
import {isTokenExpired} from "../../utils/jwt"
import {NavigateFunction} from 'react-router-dom'

export const loginUser =
    (data: ILoginRequest) =>
        async (dispatch: Dispatch<any>): Promise<void> => {
            try {
                dispatch(loginStart())

                const res = await api.auth.login(data)

                dispatch(loginSuccess(res.data.access_token))
            } catch (e: any) {
                console.error(e)

                if (e.response?.status === 422) {
                    dispatch(loginFailure({
                        validationErrors: e.response.data.errors
                    }))
                } else if (e.response?.status === 401) {
                    const errorData: IAuthError = e.response.data
                    dispatch(loginFailure({
                        error: errorData.message || 'Ошибка авторизации'
                    }))
                } else {
                    dispatch(loginFailure({
                        error: e.message || 'Произошла ошибка'
                    }))
                }
            }
        }

export const clearValidationErrorAction = (fieldName: string) => (dispatch: Dispatch<any>) => {
    dispatch(clearValidationError(fieldName))
}

export const logoutUser =
    (navigate: NavigateFunction) =>
        async (dispatch: Dispatch<any>): Promise<void> => {
            try {
                await api.auth.logout()

                dispatch(logoutSuccess())

                navigate('/')
            } catch (e: any) {
                console.error(e)
                dispatch(logoutSuccess())
                navigate('/')
            }
        }

let refreshTokenRequest: AxiosPromise<ILoginResponse> | null = null

export const getAccessToken =
    () =>
        async (dispatch: Dispatch<any>): Promise<string | null> => {
            try {
                const accessToken = store.getState().auth.authData.accessToken

                if (!accessToken || isTokenExpired(accessToken)) {
                    if (refreshTokenRequest === null) {
                        refreshTokenRequest = api.auth.refreshToken()
                    }

                    const res = await refreshTokenRequest
                    refreshTokenRequest = null

                    dispatch(loginSuccess(res.data.access_token))

                    return res.data.access_token
                }

                return accessToken
            } catch (e) {
                console.error(e)

                return null
            }
        }

export const initializeAuth =
    () =>
        async (dispatch: Dispatch<any>): Promise<void> => {
            try {
                dispatch(initAuthStart())

                const accessToken = await store.dispatch(getAccessToken())

                if (accessToken) {
                    dispatch(initAuthSuccess(accessToken))
                } else {
                    dispatch(initAuthFailure())
                }
            } catch (e) {
                dispatch(initAuthFailure())
            }
        }