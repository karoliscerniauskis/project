import { apiRequest } from './http'

export type MyVoucherView = {
    code: string
    providerName: string
}

export type MyVouchersResponse = {
    data: MyVoucherView[]
}

export function getMyVouchers(): Promise<MyVouchersResponse> {
    return apiRequest<MyVouchersResponse>('/api/me/vouchers', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}
