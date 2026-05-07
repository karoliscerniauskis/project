export type VoucherStatus =
    | 'notFound'
    | 'active'
    | 'claimed'
    | 'deactivated'
    | 'transferred'
    | 'used'
    | 'valid'
    | 'canceled'

export type ProviderStatus = 'active' | 'pending' | 'inactive'

export type VoucherInvalidReason =
    | 'voucherNotFound'
    | 'voucherDeactivated'
    | 'voucherAlreadyUsed'
    | 'voucherAlreadyClaimed'
    | 'voucherTransferred'
    | 'voucherNotActive'

export function formatVoucherStatus(status: VoucherStatus): string {
    const statusMap: Record<VoucherStatus, string> = {
        notFound: 'Not Found',
        active: 'Active',
        claimed: 'Claimed',
        deactivated: 'Deactivated',
        transferred: 'Transferred',
        used: 'Used',
        canceled: 'Canceled',
        valid: 'Valid',
    }
    return statusMap[status] || status
}

export function formatVoucherReason(reason: VoucherInvalidReason): string {
    const reasonMap: Record<VoucherInvalidReason, string> = {
        voucherNotFound: 'Voucher not found',
        voucherDeactivated: 'Voucher has been deactivated',
        voucherAlreadyUsed: 'Voucher has already been used',
        voucherAlreadyClaimed: 'Voucher has already been claimed',
        voucherTransferred: 'Voucher has been transferred',
        voucherNotActive: 'This voucher is no longer active.',
    }
    return reasonMap[reason] || reason
}

export function getVoucherStatusClasses(status: VoucherStatus): string {
    const classMap: Record<VoucherStatus, string> = {
        notFound: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        valid: 'bg-green-100 text-green-800',
        claimed: 'bg-yellow-100 text-yellow-800',
        deactivated: 'bg-red-100 text-red-800',
        transferred: 'bg-blue-100 text-blue-800',
        used: 'bg-purple-100 text-purple-800',
        canceled: 'bg-slate-100 text-slate-800',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}

export function getProviderStatusClasses(status: ProviderStatus): string {
    const classMap: Record<ProviderStatus, string> = {
        active: 'bg-green-500',
        pending: 'bg-yellow-500',
        inactive: 'bg-red-500',
    }
    return classMap[status] || 'bg-gray-500'
}
