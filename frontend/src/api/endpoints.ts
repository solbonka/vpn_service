const Endpoints = {
    AUTH: {
        LOGIN: '/admin/auth/login',
        REFRESH: '/admin/auth/refresh-token',
        LOGOUT: '/admin/auth/logout'
    },
    SERVERS: {
        LIST: '/admin/servers',
        METRICS: '/admin/metrics/servers',
        CHART_DATA: '/admin/metrics/charts/users'
    },
    MARZBAN: {
        SERVERS: '/admin/marzban/servers',
        SERVERS_METRICS: '/admin/marzban/servers/metrics'
    },
    REMNAWAVE: {
        HOSTS: '/admin/remnawave/hosts',
        HOSTS_METRICS: '/admin/remnawave/hosts/metrics'
    },
    SUBSCRIPTIONS: {
        METRICS: '/admin/metrics/subscriptions',
        CHART_DATA: '/admin/metrics/charts/subscriptions'
    },
    PAYMENTS: {
        METRICS: '/admin/metrics/payments',
        CHART_DATA: '/admin/metrics/charts/payments'
    },
    CHATS: {
        PASSIVE: '/admin/chats/passive',
        BLOCKED: '/admin/chats/blocked',
        GET: (id: number) => `/admin/chats/${id}`,
        UPDATE: (id: number) => `/admin/chats/${id}`
    },
    PROMO_CODES: {
        LIST: '/admin/promo-codes',
        AVAILABLE_DURATIONS: '/admin/promo-codes/available-durations',
        CREATE: '/admin/promo-codes',
        GET: (id: number) => `/admin/promo-codes/${id}`,
        UPDATE: (id: number) => `/admin/promo-codes/${id}`,
        DELETE: (id: number) => `/admin/promo-codes/${id}`
    }
}

export default Endpoints