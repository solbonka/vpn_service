import React, { useState, useEffect } from 'react';
import { ArrowLeft, Copy, Check, Download, ExternalLink } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

type Platform = 'windows' | 'macos' | 'ios' | 'android';

interface ConnectInstructionsPageProps {
  platform: Platform;
  clientId: string;
}

const instructions: Record<Platform, Record<string, string[]>> = {
  windows: {
    wireguard: [
      '1. Скачайте и установите WireGuard с официального сайта',
      '2. Откройте WireGuard и нажмите "Add Tunnel"',
      '3. Выберите "Add from file" или "Add empty tunnel"',
      '4. Вставьте конфигурацию VPN ключа в поле "Private Key"',
      '5. Нажмите "Save" и затем "Activate" для подключения'
    ],
    tunngle: [
      '1. Скачайте и установите Tunngle',
      '2. Запустите программу и создайте аккаунт',
      '3. В настройках найдите раздел "VPN"',
      '4. Введите данные VPN сервера',
      '5. Нажмите "Connect" для подключения'
    ]
  },
  macos: {
    wireguard: [
      '1. Скачайте WireGuard из Mac App Store или с официального сайта',
      '2. Откройте WireGuard и нажмите "+"',
      '3. Выберите "Create from file" или "Create empty tunnel"',
      '4. Вставьте конфигурацию VPN ключа',
      '5. Нажмите "Save" и активируйте туннель'
    ],
    tunnelblick: [
      '1. Скачайте и установите Tunnelblick',
      '2. Откройте Tunnelblick и нажмите "Import"',
      '3. Выберите файл конфигурации .ovpn',
      '4. Введите данные для подключения',
      '5. Нажмите "Connect" для установления соединения'
    ]
  },
  ios: {
    wireguard: [
      '1. Скачайте WireGuard из App Store',
      '2. Откройте приложение и нажмите "+"',
      '3. Выберите "Create from file" или "Add empty tunnel"',
      '4. Вставьте конфигурацию VPN ключа',
      '5. Нажмите "Save" и активируйте туннель'
    ],
    openvpn: [
      '1. Скачайте OpenVPN Connect из App Store',
      '2. Откройте приложение и нажмите "+"',
      '3. Выберите "File" и загрузите конфигурацию',
      '4. Введите данные для подключения',
      '5. Нажмите "Connect" для установления соединения'
    ]
  },
  android: {
    wireguard: [
      '1. Скачайте WireGuard из Google Play Store',
      '2. Откройте приложение и нажмите "+"',
      '3. Выберите "Create from file" или "Add empty tunnel"',
      '4. Вставьте конфигурацию VPN ключа',
      '5. Нажмите "Save" и активируйте туннель'
    ],
    openvpn: [
      '1. Скачайте OpenVPN Connect из Google Play Store',
      '2. Откройте приложение и нажмите "+"',
      '3. Выберите "File" и загрузите конфигурацию',
      '4. Введите данные для подключения',
      '5. Нажмите "Connect" для установления соединения'
    ]
  }
};

const ConnectInstructionsPage: React.FC<ConnectInstructionsPageProps> = ({ platform, clientId }) => {
  const { user, subscription, webApp, isTelegramMode } = useTelegram();
  const [vpnKey, setVpnKey] = useState<string>('');
  const [isLoading, setIsLoading] = useState(true);
  const [copied, setCopied] = useState(false);

  const clientInstructions = instructions[platform]?.[clientId] || [];

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = `connect/${platform}`;
  };

  const handleConnect = () => {
    if (webApp && isTelegramMode) {
      webApp.showAlert('VPN ключ скопирован! Теперь настройте подключение в вашем VPN клиенте.', () => {
        // Можно добавить дополнительную логику
      });
    } else {
      alert('VPN ключ скопирован! Теперь настройте подключение в вашем VPN клиенте.');
    }
  };

  const copyToClipboard = async () => {
    try {
      await navigator.clipboard.writeText(vpnKey);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy: ', err);
    }
  };

  // Имитация получения VPN ключа
  useEffect(() => {
    const fetchVpnKey = async () => {
      setIsLoading(true);
      // Временная имитация - в реальности здесь будет API запрос
      setTimeout(() => {
        if (subscription?.token) {
          setVpnKey(`[Interface]
PrivateKey = ${subscription.token}
Address = 10.0.0.2/24
DNS = 8.8.8.8

[Peer]
PublicKey = server_public_key_here
Endpoint = vpn.example.com:51820
AllowedIPs = 0.0.0.0/0`);
        }
        setIsLoading(false);
      }, 1000);
    };

    fetchVpnKey();
  }, [subscription]);

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

  const getClientName = (clientId: string) => {
    const names: Record<string, string> = {
      wireguard: 'WireGuard',
      tunngle: 'Tunngle',
      tunnelblick: 'Tunnelblick',
      openvpn: 'OpenVPN Connect'
    };
    return names[clientId] || clientId;
  };

  const getPlatformName = (platform: Platform) => {
    const names = {
      windows: 'Windows',
      macos: 'macOS',
      ios: 'iOS',
      android: 'Android'
    };
    return names[platform];
  };

  if (isLoading) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p className="text-white text-lg">Генерируем VPN ключ...</p>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="py-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-white mb-2">Инструкции по подключению</h1>
          <p className="text-gray-300">
            {getClientName(clientId)} на {getPlatformName(platform)}
          </p>
        </div>

        {/* VPN ключ */}
        <div className="mb-6">
          <div className="flex items-center justify-between mb-3">
            <h3 className="text-white font-semibold">Ваш VPN ключ:</h3>
            <button
              onClick={copyToClipboard}
              className="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors"
            >
              {copied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
              {copied ? 'Скопировано' : 'Копировать'}
            </button>
          </div>
          <div className="bg-gray-800 border border-gray-600 rounded-lg p-4">
            <pre className="text-green-400 text-sm whitespace-pre-wrap font-mono">
              {vpnKey}
            </pre>
          </div>
        </div>

        {/* Инструкции */}
        <div className="mb-6">
          <h3 className="text-white font-semibold mb-3">Пошаговые инструкции:</h3>
          <div className="space-y-3">
            {clientInstructions.map((instruction, index) => (
              <div key={index} className="bg-gray-800 border border-gray-600 rounded-lg p-4">
                <p className="text-gray-300">{instruction}</p>
              </div>
            ))}
          </div>
        </div>

        {/* Кнопка подключения */}
        <div className="mb-6">
          <button
            onClick={handleConnect}
            className="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-lg font-semibold flex items-center justify-center gap-3 transition-colors"
          >
            <ExternalLink className="w-6 h-6" />
            Подключиться к VPN
          </button>
        </div>

        {/* Дополнительная информация */}
        <div className="p-4 bg-blue-900 bg-opacity-30 rounded-lg border border-blue-700">
          <h3 className="text-blue-300 font-semibold mb-2">💡 Полезные советы</h3>
          <ul className="text-blue-200 text-sm space-y-1">
            <li>• Сохраните VPN ключ в безопасном месте</li>
            <li>• При проблемах с подключением проверьте интернет-соединение</li>
            <li>• Для отключения деактивируйте туннель в VPN клиенте</li>
          </ul>
        </div>
      </div>
    </Layout>
  );
};

export default ConnectInstructionsPage;


