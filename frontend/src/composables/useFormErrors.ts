import { ApiError, type ApiFieldError } from '@/api/http'

export function useFormErrors() {
    function extractMessage(error: unknown): string {
        if (error instanceof ApiError) {
            return error.message
        }

        if (error instanceof Error) {
            return error.message
        }

        return 'Something went wrong'
    }

    function extractFieldErrors(error: unknown): ApiFieldError[] {
        if (error instanceof ApiError && error.details && typeof error.details === 'object') {
            const details = error.details as { errors?: ApiFieldError[] }

            return Array.isArray(details.errors) ? details.errors : []
        }

        return []
    }

    return {
        extractMessage,
        extractFieldErrors,
    }
}
