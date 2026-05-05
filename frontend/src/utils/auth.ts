type JwtPayload = {
    roles?: string[]
    username?: string
}

export function isAdmin(): boolean {
    const token = localStorage.getItem('token')

    if (token === null) {
        return false
    }

    const payload = parseJwtPayload(token)

    return payload?.roles?.includes('ROLE_ADMIN') ?? false
}

export function getUsername(): string | null {
    const token = localStorage.getItem('token')

    if (token === null) {
        return null
    }

    const payload = parseJwtPayload(token)

    return payload?.username ?? null
}

function parseJwtPayload(token: string): JwtPayload | null {
    const [, payload] = token.split('.')

    if (payload === undefined) {
        return null
    }

    try {
        const normalizedPayload = payload.replace(/-/g, '+').replace(/_/g, '/')
        const decodedPayload = decodeURIComponent(
            atob(normalizedPayload)
                .split('')
                .map(char => `%${char.charCodeAt(0).toString(16).padStart(2, '0')}`)
                .join('')
        )

        return JSON.parse(decodedPayload) as JwtPayload
    } catch {
        return null
    }
}
