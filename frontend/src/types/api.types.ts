export interface ApiResponse<T> {
    data: T
}

export interface ApiError {
    message: string
    errors?: Record<string, string[]>
}
