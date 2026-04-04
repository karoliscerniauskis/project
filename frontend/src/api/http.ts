export type ApiFieldError = {
    field: string
    message: string
}

export type ApiErrorResponse = {
    message?: string
    errors?: ApiFieldError[]
}

export class ApiError extends Error {
    public readonly status: number
    public readonly details: ApiErrorResponse | string | null

    public constructor(message: string, status: number, details: ApiErrorResponse | string | null) {
        super(message)
        this.name = 'ApiError'
        this.status = status
        this.details = details
    }
}

export async function apiRequest<TResponse>(
    url: string,
    options: RequestInit,
): Promise<TResponse> {
    const headers = new Headers(options.headers)
    const token = localStorage.getItem('token')

    if (token !== null && !headers.has('Authorization')) {
        headers.set('Authorization', `Bearer ${token}`)
    }

    const response = await fetch(url, {...options, headers})

    const contentType = response.headers.get('content-type') ?? ''
    const data = contentType.includes('application/json')
        ? await response.json().catch(() => null)
        : await response.text().catch(() => null)

    if (!response.ok) {
        const message =
            typeof data === 'string'
                ? data
                : data?.message ?? 'Request failed'

        throw new ApiError(message, response.status, data)
    }

    return data as TResponse
}
