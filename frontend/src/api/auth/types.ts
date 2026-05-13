export interface ILoginRequest {
    email: string
    password: string
}

export interface ILoginResponse {
    access_token: string
}

export interface IValidationErrors {
    [key: string]: string[]
}
export interface IAuthError {
    error: string
    message: string
    errors?: IValidationErrors
}
