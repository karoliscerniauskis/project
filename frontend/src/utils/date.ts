export function formatRelativeDate(dateString: string): string {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMs = date.getTime() - now.getTime()
    const diffInDays = Math.ceil(diffInMs / (1000 * 60 * 60 * 24))

    if (diffInDays < 0) {
        return `${Math.abs(diffInDays)} days ago`
    } else if (diffInDays === 0) {
        return 'today'
    } else if (diffInDays === 1) {
        return 'tomorrow'
    } else {
        return `in ${diffInDays} days`
    }
}
