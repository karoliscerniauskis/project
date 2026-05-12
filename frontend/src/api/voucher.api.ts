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

export interface ChangeVoucherProviderRequest {
    newProviderId: string
}

export interface ImportVoucherRequest {
    code: string
}

export interface GeneratePhysicalVoucherResponse {
    imageUrl: string
}

export interface Voucher {
    id: string
    code: string | null
    providerId: string
    providerName: string
    status: string
    canBeClaimed: boolean
    canBeTransferred: boolean
    canProviderBeChanged: boolean
    isCodeVisible: boolean
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

export interface VoucherUsage {
    id: string
    usedAmount: number | null
    usedAt: string
}

export interface PaginationMeta {
    total: number
    page: number
    perPage: number
    totalPages: number
}

export interface PaginatedProviderVouchers {
    data: ProviderVoucher[]
    meta: PaginationMeta
}

export interface PaginatedVouchers {
    data: Voucher[]
    meta: PaginationMeta
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

    async getUserVouchers(code?: string, page = 1, perPage = 20): Promise<PaginatedVouchers> {
        const params = new URLSearchParams()
        if (code) params.append('code', code)
        params.append('page', page.toString())
        params.append('perPage', perPage.toString())

        return http.get<PaginatedVouchers>(`/api/me/vouchers?${params}`)
    },

    async getProviderVouchers(providerId: string, code?: string, page = 1, perPage = 20): Promise<PaginatedProviderVouchers> {
        const params = new URLSearchParams()
        if (code) params.append('code', code)
        params.append('page', page.toString())
        params.append('perPage', perPage.toString())

        return http.get<PaginatedProviderVouchers>(`/api/providers/${providerId}/vouchers?${params}`)
    },

    async useVoucher(providerId: string, data: UseVoucherRequest): Promise<void> {
        await http.post(`/api/providers/${providerId}/vouchers/use`, data)
    },

    async deactivateVoucher(providerId: string, voucherId: string): Promise<void> {
        await http.post(`/api/providers/${providerId}/vouchers/${voucherId}/deactivate`)
    },

    async changeVoucherProvider(voucherId: string, data: ChangeVoucherProviderRequest): Promise<void> {
        await http.patch(`/api/vouchers/${voucherId}/change-provider`, data)
    },

    async getVoucherUsages(voucherId: string): Promise<VoucherUsage[]> {
        const response = await http.get<{ data: VoucherUsage[] }>(`/api/vouchers/${voucherId}/usages`)
        return response.data
    },

    async importVoucher(data: ImportVoucherRequest): Promise<void> {
        await http.post('/api/vouchers/import', data)
    },

    async generatePhysicalVoucher(voucherId: string): Promise<GeneratePhysicalVoucherResponse> {
        return http.post<GeneratePhysicalVoucherResponse>(`/api/vouchers/${voucherId}/generate-physical`, {})
    },
}
