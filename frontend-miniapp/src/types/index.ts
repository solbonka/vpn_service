export interface User {
  id: number;
  first_name: string;
  last_name?: string;
  username?: string;
  language_code?: string;
  photo_url?: string;
  is_channel_subscribed?: boolean;
}

export interface VPNServer {
  id: string;
  name: string;
  country: string;
  city: string;
  flag: string;
  load: number;
  ping: number;
  status: 'online' | 'offline' | 'maintenance';
}

export interface Subscription {
  id: string;
  name: string;
  duration: number; // days
  price: number;
  currency: string;
  traffic: number; // GB
  features: string[];
  popular?: boolean;
}

export interface UserSubscription {
  id: string;
  subscription: Subscription;
  startDate: string;
  endDate: string;
  trafficUsed: number;
  status: 'active' | 'expired' | 'suspended';
}

export interface ConnectionStatus {
  connected: boolean;
  server?: VPNServer;
  startTime?: string;
  bytesReceived: number;
  bytesSent: number;
}

export interface PaymentMethod {
  id: string;
  name: string;
  icon: string;
  currency: string;
  minAmount: number;
}

export interface AuthResponse {
  success: boolean;
  user: User;
  subscription: {
    id: number;
    status: string;
    end_date: string;
    plan: string;
    duration: number;
    token: string;
  } | null;
  support_channel: string;
  lottery_enabled: boolean;
  channel_name: string;
  channel_link: string;
}

// Environment variables
declare global {
  const __APP_NAME__: string;
  const __BOT_USERNAME__: string;
}