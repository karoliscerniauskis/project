import { http } from '@/utils/http'

export interface CreateVoucherRequest {
    issuedToEmail: string
    type: 'amount' | 'usage'
    amount?: number | null
    usages?: number | null
    expiresAt?: string | null
    scheduledSendAt?: string | null
}

export interface CreateVoucherResponse {
    id: string
}

export interface ValidateVoucherRequest {
    code: string
}

export interface VoucherValidationResponse {
    valid: boolean
    status: string
    reason: string | null
}

export interface TransferVoucherRequest {
    recipientEmail: string
}

export interface Voucher {
    id: string
    code: string
    providerName: string
    status: string
    canBeClaimedOrTransferred: boolean
    type: string
    initialAmount: number | null
    remainingAmount: number | null
    initialUsages: number | null
    remainingUsages: number | null
    expiresAt: string | null
}

export interface ProviderVoucher {
    id: string
    code: string
    issuedToEmail: string
    claimedByUser: string | null
    createdByUser: string
    status: string
    type: string
    initialAmount: number | null
    remainingAmount: number | null
    initialUsages: number | null
    remainingUsages: number | null
    expiresAt: string | null
}

export interface UseVoucherRequest {
    code: string
    amount?: number | null
}

export const voucherApi = {
    async createVoucher(providerId: string, data: CreateVoucherRequest): Promise<CreateVoucherResponse> {
        return http.post<CreateVoucherResponse>(`/api/providers/${providerId}/vouchers`, data)
    },

    async validateVoucher(providerId: string, data: ValidateVoucherRequest): Promise<VoucherValidationResponse> {
        const response = await http.post<{ data: VoucherValidationResponse }>(
            `/api/providers/${providerId}/vouchers/validate`,
            data
        )
        return response.data
    },

    async claimVoucher(voucherId: string): Promise<void> {
        await http.post(`/api/vouchers/${voucherId}/claim`)
    },

    async transferVoucher(voucherId: string, data: TransferVoucherRequest): Promise<void> {
        await http.post(`/api/vouchers/${voucherId}/transfer`, data)
    },

    async getUserVouchers(): Promise<Voucher[]> {
        const response = await http.get<{ data: Voucher[] }>('/api/me/vouchers')
        return response.data
    },

    async getProviderVouchers(providerId: string): Promise<ProviderVoucher[]> {
        const response = await http.get<{ data: ProviderVoucher[] }>(`/api/providers/${providerId}/vouchers`)
        return response.data
    },

    async useVoucher(providerId: string, data: UseVoucherRequest): Promise<void> {
        await http.post(`/api/providers/${providerId}/vouchers/use`, data)
    },

    async deactivateVoucher(providerId: string, voucherId: string): Promise<void> {
        await http.post(`/api/providers/${providerId}/vouchers/${voucherId}/deactivate`)
    },
}
