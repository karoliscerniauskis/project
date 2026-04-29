/**
 * Status formatting and styling utilities
 */

export type VoucherStatus =
    | 'notFound'
    | 'active'
    | 'claimed'
    | 'deactivated'
    | 'transferred'
    | 'used'
    | 'canceled'

export type ProviderStatus = 'active' | 'pending' | 'inactive'

export type VoucherInvalidReason =
    | 'voucherNotFound'
    | 'voucherDeactivated'
    | 'voucherAlreadyUsed'
    | 'voucherAlreadyClaimed'
    | 'voucherTransferred'

/**
 * Format voucher status to human-readable text
 */
export function formatVoucherStatus(status: VoucherStatus): string {
    const statusMap: Record<VoucherStatus, string> = {
        notFound: 'Not Found',
        active: 'Active',
        claimed: 'Claimed',
        deactivated: 'Deactivated',
        transferred: 'Transferred',
        used: 'Used',
        canceled: 'Canceled',
    }
    return statusMap[status] || status
}

/**
 * Format voucher invalid reason to human-readable text
 */
export function formatVoucherReason(reason: VoucherInvalidReason): string {
    const reasonMap: Record<VoucherInvalidReason, string> = {
        voucherNotFound: 'Voucher not found',
        voucherDeactivated: 'Voucher has been deactivated',
        voucherAlreadyUsed: 'Voucher has already been used',
        voucherAlreadyClaimed: 'Voucher has already been claimed',
        voucherTransferred: 'Voucher has been transferred',
    }
    return reasonMap[reason] || reason
}

/**
 * Get Tailwind CSS classes for voucher status badge
 */
export function getVoucherStatusClasses(status: VoucherStatus): string {
    const classMap: Record<VoucherStatus, string> = {
        notFound: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        claimed: 'bg-yellow-100 text-yellow-800',
        deactivated: 'bg-red-100 text-red-800',
        transferred: 'bg-blue-100 text-blue-800',
        used: 'bg-purple-100 text-purple-800',
        canceled: 'bg-slate-100 text-slate-800',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}

/**
 * Get Tailwind CSS classes for provider status indicator (dot)
 */
export function getProviderStatusClasses(status: ProviderStatus): string {
    const classMap: Record<ProviderStatus, string> = {
        active: 'bg-green-500',
        pending: 'bg-yellow-500',
        inactive: 'bg-red-500',
    }
    return classMap[status] || 'bg-gray-500'
}
