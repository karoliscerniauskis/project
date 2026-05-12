export interface ApiResponse<T> {
    data: T
}

export interface ApiError {
    message: string
    errors?: Array<{ field: string; message: string }>
}
