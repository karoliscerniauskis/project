export type StatusType = 'success' | 'info' | 'warning' | 'danger'

export function getVoucherStatusType(status: string): StatusType {
    switch (status) {
        case 'active':
            return 'success'
        case 'used':
            return 'info'
        case 'canceled':
            return 'danger'
        default:
            return 'info'
    }
}

export function getProviderStatusType(status: string): StatusType {
    switch (status) {
        case 'active':
            return 'success'
        case 'pending':
            return 'warning'
        case 'inactive':
            return 'danger'
        default:
            return 'info'
    }
}
