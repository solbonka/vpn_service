import {AxiosPromise} from "axios";
import Endpoints from "./endpoints";
import {axiosInstance} from "./instance";

export const login = (params: any): AxiosPromise =>
    axiosInstance.post(Endpoints.AUTH.LOGIN, params)

export const logout = (): AxiosPromise => {
    return axiosInstance.post(Endpoints.AUTH.LOGOUT)
}

export const refreshToken = (): AxiosPromise =>
    axiosInstance.post(Endpoints.AUTH.REFRESH)

export const getServers = (): AxiosPromise =>
    axiosInstance.get(Endpoints.SERVERS.LIST)

export const getServersMetrics = (): AxiosPromise =>
    axiosInstance.get(Endpoints.SERVERS.METRICS)

// Marzban API methods
export const getMarzbanServers = (): AxiosPromise =>
    axiosInstance.get(Endpoints.MARZBAN.SERVERS)

export const getMarzbanServersMetrics = (): AxiosPromise =>
    axiosInstance.get(Endpoints.MARZBAN.SERVERS_METRICS)

// Remnawave API methods
export const getRemnawaveHosts = (): AxiosPromise =>
    axiosInstance.get(Endpoints.REMNAWAVE.HOSTS)

export const getRemnawaveHostsMetrics = (): AxiosPromise =>
    axiosInstance.get(Endpoints.REMNAWAVE.HOSTS_METRICS)

export const getSubscriptionsMetrics = (): AxiosPromise =>
    axiosInstance.get(Endpoints.SUBSCRIPTIONS.METRICS)

export const getPaymentsMetrics = (): AxiosPromise =>
    axiosInstance.get(Endpoints.PAYMENTS.METRICS)

export const getUsersChartData = (period: string = '7d', startDate?: string, endDate?: string): AxiosPromise => {
    const params = new URLSearchParams({ period });
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    return axiosInstance.get(`${Endpoints.SERVERS.CHART_DATA}?${params.toString()}`);
}

export const getSubscriptionsChartData = (period: string = '7d', startDate?: string, endDate?: string): AxiosPromise => {
    const params = new URLSearchParams({ period });
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    return axiosInstance.get(`${Endpoints.SUBSCRIPTIONS.CHART_DATA}?${params.toString()}`);
}

export const getPaymentsChartData = (period: string = '7d', startDate?: string, endDate?: string): AxiosPromise => {
    const params = new URLSearchParams({ period });
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    return axiosInstance.get(`${Endpoints.PAYMENTS.CHART_DATA}?${params.toString()}`);
}

export const getMiniappSettings = (): AxiosPromise =>
    axiosInstance.get('/admin/miniapp-settings')

export const updateMiniappLogo = (logo: string): AxiosPromise =>
    axiosInstance.put('/admin/miniapp-settings/logo', { logo })

export const deleteMiniappLogo = (): AxiosPromise =>
    axiosInstance.delete('/admin/miniapp-settings/logo')

export const updateLotteryImage = (lottery_prize_image: string): AxiosPromise =>
    axiosInstance.put('/admin/miniapp-settings/lottery-image', { lottery_prize_image })

export const deleteLotteryImage = (): AxiosPromise =>
    axiosInstance.delete('/admin/miniapp-settings/lottery-image')

// Referral settings API
export const getReferralSettings = (): AxiosPromise =>
    axiosInstance.get('/admin/referral-settings')

export const createBonusType = (data: any): AxiosPromise =>
    axiosInstance.post('/admin/referral-settings', data)

export const updateBonusType = (id: number, data: any): AxiosPromise =>
    axiosInstance.put(`/admin/referral-settings/${id}`, data)

export const deleteBonusType = (id: number): AxiosPromise =>
    axiosInstance.delete(`/admin/referral-settings/${id}`)

export const activateBonusType = (id: number): AxiosPromise =>
    axiosInstance.post(`/admin/referral-settings/${id}/activate`)

export const getReferralStats = (): AxiosPromise =>
    axiosInstance.get('/admin/referral-settings/stats')

export const getPassiveUsers = (page: number = 1): AxiosPromise =>
    axiosInstance.get(`${Endpoints.CHATS.PASSIVE}?page=${page}`)

export const getBlockedUsers = (page: number = 1, isTrial?: boolean): AxiosPromise => {
    const params = new URLSearchParams({ page: page.toString() });
    if (isTrial !== undefined) {
        params.append('is_trial', isTrial.toString());
    }
    return axiosInstance.get(`${Endpoints.CHATS.BLOCKED}?${params.toString()}`);
}

export const getChat = (chatId: number): AxiosPromise =>
    axiosInstance.get(Endpoints.CHATS.GET(chatId))

export const updateChat = (chatId: number, data: { is_recovery_processed: boolean }): AxiosPromise =>
    axiosInstance.patch(Endpoints.CHATS.UPDATE(chatId), data)

// Promo codes API
export const getPromoCodes = (): AxiosPromise =>
    axiosInstance.get(Endpoints.PROMO_CODES.LIST)

export const getAvailableDurations = (): AxiosPromise =>
    axiosInstance.get(Endpoints.PROMO_CODES.AVAILABLE_DURATIONS)

export const getPromoCode = (id: number): AxiosPromise =>
    axiosInstance.get(Endpoints.PROMO_CODES.GET(id))

export const createPromoCode = (data: any): AxiosPromise =>
    axiosInstance.post(Endpoints.PROMO_CODES.CREATE, data)

export const updatePromoCode = (id: number, data: any): AxiosPromise =>
    axiosInstance.put(Endpoints.PROMO_CODES.UPDATE(id), data)

export const deletePromoCode = (id: number): AxiosPromise =>
    axiosInstance.delete(Endpoints.PROMO_CODES.DELETE(id))

const api = {
    auth: {
        login,
        logout,
        refreshToken
    },
    servers: {
        getServers,
        getServersMetrics,
        getUsersChartData
    },
    marzban: {
        getServers: getMarzbanServers,
        getServersMetrics: getMarzbanServersMetrics
    },
    remnawave: {
        getHosts: getRemnawaveHosts,
        getHostsMetrics: getRemnawaveHostsMetrics
    },
    subscriptions: {
        getSubscriptionsMetrics,
        getSubscriptionsChartData
    },
    payments: {
        getPaymentsMetrics,
        getPaymentsChartData
    },
    miniappSettings: {
        getMiniappSettings,
        updateMiniappLogo,
        deleteMiniappLogo,
        updateLotteryImage,
        deleteLotteryImage
    },
    referralSettings: {
        getReferralSettings,
        createBonusType,
        updateBonusType,
        deleteBonusType,
        activateBonusType,
        getReferralStats
    },
    chats: {
        getPassiveUsers,
        getBlockedUsers,
        getChat,
        updateChat
    },
    promoCodes: {
        getPromoCodes,
        getAvailableDurations,
        getPromoCode,
        createPromoCode,
        updatePromoCode,
        deletePromoCode
    },
    // Добавляем общий метод get для совместимости
    get: (url: string, config?: any) => axiosInstance.get(url, config)
}

export default api