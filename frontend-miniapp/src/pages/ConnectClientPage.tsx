import React from 'react';
import { ArrowLeft, Download, ExternalLink } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

type Platform = 'windows' | 'macos' | 'ios' | 'android';

interface VpnClient {
  id: string;
  name: string;
  description: string;
  icon: string;
  downloadUrl?: string;
  appStoreUrl?: string;
  playStoreUrl?: string;
  recommended?: boolean;
}

interface ConnectClientPageProps {
  platform: Platform;
}

const vpnClients: Record<Platform, VpnClient[]> = {
  windows: [
    {
      id: 'wireguard',
      name: 'WireGuard',
      description: 'Официальный клиент WireGuard',
      icon: '🔒',
      downloadUrl: 'https://www.wireguard.com/install/',
      recommended: true
    },
    {
      id: 'tunngle',
      name: 'Tunngle',
      description: 'Альтернативный клиент',
      icon: '🌐',
      downloadUrl: 'https://tunngle.net/'
    }
  ],
  macos: [
    {
      id: 'wireguard',
      name: 'WireGuard',
      description: 'Официальный клиент WireGuard',
      icon: '🔒',
      downloadUrl: 'https://www.wireguard.com/install/',
      recommended: true
    },
    {
      id: 'tunnelblick',
      name: 'Tunnelblick',
      description: 'OpenVPN клиент для macOS',
      icon: '🔐',
      downloadUrl: 'https://tunnelblick.net/'
    }
  ],
  ios: [
    {
      id: 'wireguard',
      name: 'WireGuard',
      description: 'Официальный клиент WireGuard',
      icon: '🔒',
      appStoreUrl: 'https://apps.apple.com/app/wireguard/id1441195209',
      recommended: true
    },
    {
      id: 'openvpn',
      name: 'OpenVPN Connect',
      description: 'OpenVPN клиент',
      icon: '🔐',
      appStoreUrl: 'https://apps.apple.com/app/openvpn-connect/id590379981'
    }
  ],
  android: [
    {
      id: 'wireguard',
      name: 'WireGuard',
      description: 'Официальный клиент WireGuard',
      icon: '🔒',
      playStoreUrl: 'https://play.google.com/store/apps/details?id=com.wireguard.android',
      recommended: true
    },
    {
      id: 'openvpn',
      name: 'OpenVPN Connect',
      description: 'OpenVPN клиент',
      icon: '🔐',
      playStoreUrl: 'https://play.google.com/store/apps/details?id=net.openvpn.openvpn'
    }
  ]
};

const ConnectClientPage: React.FC<ConnectClientPageProps> = ({ platform }) => {
  const { webApp, isTelegramMode } = useTelegram();
  const clients = vpnClients[platform];

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = 'connect';
  };

  const handleClientSelect = (clientId: string) => {
    // Переходим к инструкциям
    window.location.hash = `connect/${platform}/${clientId}`;
  };

  const handleDownload = (client: VpnClient, e: React.MouseEvent) => {
    e.stopPropagation();
    if (client.downloadUrl) {
      window.open(client.downloadUrl, '_blank');
    } else if (client.appStoreUrl) {
      window.open(client.appStoreUrl, '_blank');
    } else if (client.playStoreUrl) {
      window.open(client.playStoreUrl, '_blank');
    }
  };

  React.useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [webApp, isTelegramMode]);

  const getPlatformName = (platform: Platform) => {
    const names = {
      windows: 'Windows',
      macos: 'macOS',
      ios: 'iOS',
      android: 'Android'
    };
    return names[platform];
  };

  return (
    <Layout>
      <div className="py-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-white mb-2">Выберите VPN клиент</h1>
          <p className="text-gray-300">Выберите VPN клиент для {getPlatformName(platform)}</p>
        </div>

        <div className="space-y-3">
          {clients.map((client) => (
            <div
              key={client.id}
              className="bg-gray-800 border border-gray-600 rounded-lg p-4"
            >
              <div className="flex items-center gap-4">
                <div className="text-3xl">{client.icon}</div>
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="text-white font-semibold text-lg">{client.name}</h3>
                    {client.recommended && (
                      <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                        Рекомендуется
                      </span>
                    )}
                  </div>
                  <p className="text-gray-400 text-sm">{client.description}</p>
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={(e) => handleDownload(client, e)}
                    className="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors"
                  >
                    <Download className="w-4 h-4" />
                    Скачать
                  </button>
                  <button
                    onClick={() => handleClientSelect(client.id)}
                    className="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors"
                  >
                    <ExternalLink className="w-4 h-4" />
                    Настроить
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        <div className="mt-8 p-4 bg-yellow-900 bg-opacity-30 rounded-lg border border-yellow-700">
          <h3 className="text-yellow-300 font-semibold mb-2">⚠️ Важно</h3>
          <p className="text-yellow-200 text-sm">
            Сначала скачайте и установите выбранный VPN клиент, 
            затем нажмите "Настроить" для получения инструкций по подключению.
          </p>
        </div>
      </div>
    </Layout>
  );
};

export default ConnectClientPage;


