const STORAGE_KEYS = {
    ACCESS_TOKEN: 'access_token',
    REFRESH_TOKEN: 'refresh_token',
} as const

export const storage = {
    getAccessToken(): string | null {
        return localStorage.getItem(STORAGE_KEYS.ACCESS_TOKEN)
    },

    setAccessToken(token: string): void {
        localStorage.setItem(STORAGE_KEYS.ACCESS_TOKEN, token)
    },

    getRefreshToken(): string | null {
        return localStorage.getItem(STORAGE_KEYS.REFRESH_TOKEN)
    },

    setRefreshToken(token: string): void {
        localStorage.setItem(STORAGE_KEYS.REFRESH_TOKEN, token)
    },

    clearTokens(): void {
        localStorage.removeItem(STORAGE_KEYS.ACCESS_TOKEN)
        localStorage.removeItem(STORAGE_KEYS.REFRESH_TOKEN)
    },

    hasTokens(): boolean {
        return Boolean(this.getAccessToken() && this.getRefreshToken())
    },
}
