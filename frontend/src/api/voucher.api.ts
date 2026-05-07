import { apiRequest } from './http'

export type MyVoucherView = {
    id: string
    code: string | null
    providerName: string
    status: string
    canBeClaimedOrTransferred: boolean
    type: 'amount' | 'usage'
    initialAmount: number | null
    remainingAmount: number | null
    initialUsages: number | null
    remainingUsages: number | null
}

export type VoucherValidationView = {
    valid: boolean
    status: string
    reason: string | null
}

export type ValidateVoucherPayload = {
    code: string
}

export type MyVouchersResponse = {
    data: MyVoucherView[]
}

export type ValidateVoucherResponse = {
    data: VoucherValidationView
}

export type TransferVoucherPayload = {
    recipientEmail: string
}

export function validateVoucher(
    providerId: string,
    payload: ValidateVoucherPayload
): Promise<ValidateVoucherResponse> {
    return apiRequest<ValidateVoucherResponse>(`/api/providers/${providerId}/vouchers/validate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function getMyVouchers(): Promise<MyVouchersResponse> {
    return apiRequest<MyVouchersResponse>('/api/me/vouchers', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function claimVoucher(voucherId: string): Promise<void> {
    return apiRequest<void>(`/api/vouchers/${encodeURIComponent(voucherId)}/claim`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function transferVoucher(voucherId: string, payload: TransferVoucherPayload): Promise<void> {
    return apiRequest<void>(`/api/vouchers/${encodeURIComponent(voucherId)}/transfer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}
