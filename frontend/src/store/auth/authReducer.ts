import {createSlice, PayloadAction} from '@reduxjs/toolkit'
import {IValidationErrors} from '../../api/auth/types'

export interface AuthState {
    authData: {
        accessToken: string | null
        isLoading: boolean
        error: string | null
        validationErrors: IValidationErrors | null
        isInitialized: boolean
    }
}

const initialState: AuthState = {
    authData: {
        accessToken: null,
        isLoading: false,
        error: null,
        validationErrors: null,
        isInitialized: false
    }
}

export const authReducer = createSlice({
    name: 'auth',
    initialState,
    reducers: {
        loginStart: (state): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                isLoading: true
            }
        }),
        loginSuccess: (state, action: PayloadAction<string>): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                accessToken: action.payload,
                isLoading: false,
                error: null,
                validationErrors: null
            }
        }),
        loginFailure: (state, action: PayloadAction<{error?: string, validationErrors?: IValidationErrors}>): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                isLoading: false,
                error: action.payload.error || null,
                validationErrors: action.payload.validationErrors || null
            }
        }),

        logoutSuccess: (): AuthState => ({
            authData: {
                ...initialState.authData,
                isInitialized: true
            }
        }),

        initAuthStart: (state): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                isInitialized: false
            }
        }),
        initAuthSuccess: (state, action: PayloadAction<string | null>): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                accessToken: action.payload,
                isInitialized: true,
                isLoading: false,
                error: null
            }
        }),
        initAuthFailure: (state): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                accessToken: null,
                isInitialized: true,
                isLoading: false,
                error: null
            }
        }),
        
        clearValidationError: (state, action: PayloadAction<string>): AuthState => ({
            ...state,
            authData: {
                ...state.authData,
                validationErrors: state.authData.validationErrors 
                    ? Object.fromEntries(
                        Object.entries(state.authData.validationErrors).filter(([key]) => key !== action.payload)
                    ) as IValidationErrors
                    : null
            }
        }),
        

    }
})

export const {
    loginStart,
    loginSuccess,
    loginFailure,
    logoutSuccess,
    initAuthStart,
    initAuthSuccess,
    initAuthFailure,
    clearValidationError
} = authReducer.actions

export default authReducer.reducer