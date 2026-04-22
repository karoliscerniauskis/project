import { apiRequest } from './http'

export type MyVoucherView = {
    id: string
    code: string | null
    providerName: string
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

export function validateVoucher(providerId: string, payload: ValidateVoucherPayload): Promise<ValidateVoucherResponse> {
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
