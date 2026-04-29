import { ref, onMounted } from 'vue'

interface UseAsyncDataOptions<T> {
    immediate?: boolean
    onSuccess?: (data: T) => void
    onError?: (error: string) => void
}

export function useAsyncData<T>(fetchFn: () => Promise<T>, options: UseAsyncDataOptions<T> = {}) {
    const loading = ref(false)
    const error = ref('')
    const data = ref<T | null>(null)

    async function execute() {
        loading.value = true
        error.value = ''

        try {
            const result = await fetchFn()
            data.value = result
            if (options.onSuccess) {
                options.onSuccess(result)
            }
            return result
        } catch (e) {
            const errorMessage = e instanceof Error ? e.message : 'An error occurred'
            error.value = errorMessage
            if (options.onError) {
                options.onError(errorMessage)
            }
            throw e
        } finally {
            loading.value = false
        }
    }

    if (options.immediate !== false) {
        onMounted(() => execute())
    }

    return {
        loading,
        error,
        data,
        execute,
        refresh: execute,
    }
}
