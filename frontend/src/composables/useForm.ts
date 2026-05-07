import { ref } from 'vue'
import { useFormErrors } from './useFormErrors'
import type { ApiFieldError } from '@/api/http'

export function useForm() {
    const submitting = ref(false)
    const formError = ref('')
    const fieldErrors = ref<ApiFieldError[]>([])

    const { extractMessage, extractFieldErrors } = useFormErrors()

    function resetErrors() {
        formError.value = ''
        fieldErrors.value = []
    }

    async function handleSubmit<T>(
        submitFn: () => Promise<T>,
        onSuccess?: (result: T) => void | Promise<void>
    ) {
        submitting.value = true
        resetErrors()

        try {
            const result = await submitFn()
            if (onSuccess) {
                await onSuccess(result)
            }
            return result
        } catch (e) {
            formError.value = extractMessage(e)
            fieldErrors.value = extractFieldErrors(e)
            throw e
        } finally {
            submitting.value = false
        }
    }

    function validateRequired(
        field: string,
        value: string | number | null | undefined,
        label?: string
    ): boolean {
        if (value === null || value === undefined || String(value).trim() === '') {
            fieldErrors.value.push({
                field,
                message: `${label || field} is required.`,
            })

            return false
        }

        return true
    }

    function hasFieldError(field: string): boolean {
        return fieldErrors.value.some(e => e.field === field)
    }

    function getFieldError(field: string): string | undefined {
        return fieldErrors.value.find(e => e.field === field)?.message
    }

    return {
        submitting,
        formError,
        fieldErrors,
        resetErrors,
        handleSubmit,
        validateRequired,
        hasFieldError,
        getFieldError,
    }
}
