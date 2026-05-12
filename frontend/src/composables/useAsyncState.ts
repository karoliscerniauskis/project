import { ref } from 'vue'
import { ElMessage } from 'element-plus'

export interface FieldError {
    field: string
    message: string
}

export function useAsyncState<T = unknown>() {
    const data = ref<T | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    async function execute(
        asyncFn: () => Promise<T>,
        options?: {
            onSuccess?: (data: T) => void
            onError?: (error: Error) => void
            successMessage?: string
            errorMessage?: string
        }
    ): Promise<T | null> {
        loading.value = true
        error.value = null

        try {
            const result = await asyncFn()
            data.value = result

            if (options?.successMessage) {
                ElMessage.success(options.successMessage)
            }

            options?.onSuccess?.(result)
            return result
        } catch (err) {
            const errorMessage =
                (err instanceof Error ? err.message : null) || options?.errorMessage || 'An error occurred'
            error.value = errorMessage
            ElMessage.error(errorMessage)

            options?.onError?.(err instanceof Error ? err : new Error(String(err)))
            return null
        } finally {
            loading.value = false
        }
    }

    function reset() {
        data.value = null
        error.value = null
        loading.value = false
    }

    return {
        data,
        loading,
        error,
        execute,
        reset,
    }
}

export function useAsyncAction() {
    const loading = ref(false)
    const error = ref<string | null>(null)
    const success = ref<string | null>(null)
    const fieldErrors = ref<FieldError[]>([])

    async function execute(
        asyncFn: () => Promise<void>,
        options?: {
            successMessage?: string
            errorMessage?: string
        }
    ): Promise<boolean> {
        loading.value = true
        error.value = null
        success.value = null
        fieldErrors.value = []

        try {
            await asyncFn()

            if (options?.successMessage) {
                success.value = options.successMessage
                ElMessage.success(options.successMessage)
            }

            return true
        } catch (err) {
            if (err instanceof Error) {
                const match = err.message.match(/^(.+?) - (.+)$/)
                if (match) {
                    const [, baseMessage, fieldsStr] = match
                    error.value = baseMessage
                    const fieldMatches = fieldsStr.matchAll(/(\w+): ([^,]+)(?:, |$)/g)
                    fieldErrors.value = Array.from(fieldMatches).map(([, field, message]) => ({
                        field,
                        message: message.trim(),
                    }))
                } else {
                    error.value = err.message
                }
            } else {
                error.value = options?.errorMessage || 'An error occurred'
            }

            if (!error.value) {
                error.value = options?.errorMessage || 'An error occurred'
            }

            ElMessage.error(error.value)

            return false
        } finally {
            loading.value = false
        }
    }

    function reset() {
        error.value = null
        success.value = null
        fieldErrors.value = []
        loading.value = false
    }

    return {
        loading,
        error,
        success,
        fieldErrors,
        execute,
        reset,
    }
}
