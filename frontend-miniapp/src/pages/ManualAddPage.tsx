import React, { useState, useEffect } from 'react';
import { ArrowLeft, Copy, Check, ExternalLink } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

interface ConnectData {
  os: {
    id: number;
    name: string;
    slug: string;
  };
  client_app: {
    id: number;
    name: string;
    display_name: string;
  };
  download_links: any[];
  instructions: {
    setup: {
      url: string;
    };
    connection: {
      title: string;
      url: string;
    };
  };
  auto_connect?: string;
  vpn_key?: string;
}

const ManualAddPage: React.FC = () => {
  const { user, subscription, webApp, isTelegramMode } = useTelegram();
  const [connectData, setConnectData] = useState<ConnectData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [copiedStep, setCopiedStep] = useState<number | null>(null);

  // Получаем OS из URL
  const getOSFromURL = (): string => {
    const hash = window.location.hash;
    const match = hash.match(/manual-add\/([^\/]+)/);
    return match ? match[1] : '';
  };

  // Загружаем данные подключения
  const loadConnectData = async () => {
    const os = getOSFromURL();
    
    // Получаем токен из subscription или из localStorage
    const subscriptionToken = subscription?.token || localStorage.getItem('subscription_token');
    
    if (!os || !subscriptionToken) {
      setError('Данные подключения не найдены');
      setIsLoading(false);
      return;
    }

    try {
      const response = await api.get(`/miniapp/connect/${os}`, {
        headers: {
          'X-Sub-Token': subscriptionToken
        }
      });

      const data = response.data;
      
      if (data.success) {
        setConnectData(data);
      } else {
        setError(`Ошибка загрузки данных: ${data.error || 'Неизвестная ошибка'}`);
      }
    } catch (err) {
      console.error('Error loading connect data:', err);
      setError('Ошибка загрузки данных подключения');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadConnectData();

    if (webApp && isTelegramMode) {
      webApp.ready();
      webApp.expand();
    }
  }, []);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    window.history.back();
  };

  const copyToClipboard = async (text: string, stepNumber: number) => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    
    try {
      await navigator.clipboard.writeText(text);
      setCopiedStep(stepNumber);
      setTimeout(() => setCopiedStep(null), 2000);
    } catch (err) {
      console.error('Failed to copy text: ', err);
    }
  };

  // Генерируем инструкции в зависимости от ОС и VPN клиента
  const getInstructions = () => {
    if (!connectData) return [];

    const { os, client_app, vpn_key } = connectData;
    const osSlug = os.slug;
    const clientName = client_app.name.toLowerCase();

    const instructions = [];

    if (osSlug === 'ios') {
      if (clientName === 'v2raytun') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение v2RayTun',
            description: 'Найдите иконку приложения на главном экране iPhone/iPad',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления новой конфигурации',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Добавить из буфера"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Happ',
            description: 'Найдите иконку приложения на главном экране',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления нового подключения',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Вставить из буфера обмена"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      }
    } else if (osSlug === 'android') {
      if (clientName === 'v2raytun') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение v2RayTun',
            description: 'Найдите приложение в списке установленных',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления новой конфигурации',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Импорт из буфера обмена"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'hiddify') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Hiddify',
            description: 'Запустите приложение на Android устройстве',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Перейдите на вкладку "Главная"',
            description: 'Найдите и нажмите на вкладку "Главная" в приложении',
            icon: '🏠'
          },
          {
            step: 4,
            title: 'Нажмите "Новый профиль"',
            description: 'Нажмите на кнопку создания нового профиля',
            icon: '➕'
          },
          {
            step: 5,
            title: 'Выберите "Добавить из буфера обмена"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 6,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 7,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Happ',
            description: 'Найдите приложение в списке установленных',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления нового подключения',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Из буфера"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      }
    } else if (osSlug === 'huawei') {
      if (clientName === 'v2raytun') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение v2RayTun',
            description: 'Найдите приложение в списке установленных на устройстве Huawei',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления новой конфигурации',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Импорт из буфера обмена"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'hiddify') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Hiddify',
            description: 'Запустите приложение на устройстве Huawei',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Перейдите на вкладку "Главная"',
            description: 'Найдите и нажмите на вкладку "Главная" в приложении',
            icon: '🏠'
          },
          {
            step: 4,
            title: 'Нажмите "Новый профиль"',
            description: 'Нажмите на кнопку создания нового профиля',
            icon: '➕'
          },
          {
            step: 5,
            title: 'Выберите "Добавить из буфера обмена"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 6,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 7,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Happ',
            description: 'Найдите приложение в списке установленных на устройстве Huawei',
            icon: '📱'
          },
          {
            step: 3,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления нового подключения',
            icon: '➕'
          },
          {
            step: 4,
            title: 'Выберите "Из буфера"',
            description: 'Нажмите на опцию импорта конфигурации из буфера обмена',
            icon: '📋'
          },
          {
            step: 5,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      }
    } else if (osSlug === 'mac') {
      if (clientName === 'hiddify') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Hiddify',
            description: 'Запустите приложение из папки Applications',
            icon: '💻'
          },
          {
            step: 3,
            title: 'Перейдите на вкладку "Главная"',
            description: 'Найдите и нажмите на вкладку "Главная" в приложении',
            icon: '🏠'
          },
          {
            step: 4,
            title: 'Нажмите "Новый профиль"',
            description: 'Нажмите на кнопку создания нового профиля',
            icon: '➕'
          },
          {
            step: 5,
            title: 'Выберите "Добавить из буфера обмена"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 6,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 7,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Happ',
            description: 'Запустите приложение из папки Applications',
            icon: '💻'
          },
          {
            step: 3,
            title: 'Выберите "Из буфера"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 4,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 5,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      }
    } else if (osSlug === 'windows') {
      if (clientName === 'hiddify') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Hiddify',
            description: 'Запустите приложение на рабочем столе',
            icon: '🖥️'
          },
          {
            step: 3,
            title: 'Перейдите на вкладку "Главная"',
            description: 'Найдите и нажмите на вкладку "Главная" в приложении',
            icon: '🏠'
          },
          {
            step: 4,
            title: 'Нажмите "Новый профиль"',
            description: 'Нажмите на кнопку создания нового профиля',
            icon: '➕'
          },
          {
            step: 5,
            title: 'Выберите "Добавить из буфера обмена"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 6,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 7,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      } else if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Скопируйте VPN ключ',
            description: 'Нажмите на кнопку копирования ниже, чтобы скопировать ключ в буфер обмена',
            icon: '📋',
            copyable: vpn_key
          },
          {
            step: 2,
            title: 'Откройте приложение Happ',
            description: 'Запустите приложение Happ на рабочем столе',
            icon: '🖥️'
          },
          {
            step: 3,
            title: 'Выберите "Из буфера"',
            description: 'В появившемся окне выберите импорт из буфера обмена',
            icon: '📋'
          },
          {
            step: 4,
            title: 'Вставьте VPN ключ',
            description: 'Вернитесь в приложение и вставьте скопированный ключ',
            icon: '🔑'
          },
          {
            step: 5,
            title: 'Подключитесь к VPN',
            description: 'Нажмите на круглую кнопку включения для активации VPN',
            icon: '🔌'
          }
        );
      }
    } else if (osSlug === 'android_tv') {
      if (clientName === 'happ') {
        instructions.push(
          {
            step: 1,
            title: 'Откройте приложение Happ на Android TV',
            description: 'Запустите приложение на телевизоре',
            icon: '📺'
          },
          {
            step: 2,
            title: 'Нажмите кнопку "+"',
            description: 'Нажмите на кнопку добавления подписки',
            icon: '➕'
          },
          {
            step: 3,
            title: 'Получите QR код',
            description: 'На экране телевизора появится QR код для синхронизации',
            icon: '📱'
          },
          {
            step: 4,
            title: 'Откройте Happ на телефоне',
            description: 'Запустите приложение Happ на вашем смартфоне',
            icon: '📱'
          },
          {
            step: 5,
            title: 'Отсканируйте QR код',
            description: 'Нажмите "+" → "QR-код" → отсканируйте код с телевизора',
            icon: '📷'
          },
          {
            step: 6,
            title: 'Подключитесь к VPN',
            description: 'Нажмите большую кнопку включения в центре для подключения',
            icon: '🔌'
          }
        );
      }
    }

    return instructions;
  };

  if (isLoading) {
    return (
      <Layout>
        <div style={{ 
          display: 'flex', 
          justifyContent: 'center', 
          alignItems: 'center', 
          minHeight: '50vh',
          flexDirection: 'column',
          gap: '16px'
        }}>
          <div style={{ 
            width: '40px', 
            height: '40px', 
            border: '3px solid var(--accent-yellow)', 
            borderTop: '3px solid transparent',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite'
          }}></div>
          <p className="elderly-text" style={{ color: 'var(--text-primary)' }}>
            Загружаем инструкции...
          </p>
        </div>
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout>
        <div style={{ padding: '20px' }}>
          <div className="simple-card" style={{ textAlign: 'center' }}>
            <h2 className="elderly-title" style={{ color: 'var(--text-dark-strong)', marginBottom: '16px' }}>
              Ошибка
            </h2>
            <p className="elderly-text" style={{ color: 'var(--text-dark)', marginBottom: '20px' }}>
              {error}
            </p>
            <button
              onClick={handleBack}
              className="vpn-button vpn-button-secondary"
            >
              <ArrowLeft className="w-5 h-5" />
              <span>Назад</span>
            </button>
          </div>
        </div>
      </Layout>
    );
  }

  const instructions = getInstructions();

  return (
    <Layout>
      <div className="elderly-friendly" style={{ padding: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '24px' }}>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{ 
              width: 'auto', 
              padding: '12px', 
              marginBottom: '0',
              marginRight: '12px'
            }}
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <h1 className="elderly-title" style={{ fontSize: '24px', margin: '0', color: 'var(--text-primary)' }}>
            Добавление вручную
          </h1>
        </div>


        {/* Instructions */}
        <div className="simple-card">
          <h2 className="elderly-title" style={{ 
            fontSize: '20px', 
            margin: '0 0 20px 0', 
            color: 'var(--text-dark-strong)',
            textAlign: 'center'
          }}>
            Пошаговые инструкции
          </h2>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            {instructions.map((instruction, index) => (
              <div key={instruction.step} className="glass-card" style={{ padding: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                  {/* Step Number */}
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    background: 'var(--gradient-yellow)', 
                    borderRadius: '50%', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center',
                    color: 'var(--text-dark)',
                    fontWeight: '600',
                    fontSize: '14px',
                    flexShrink: 0,
                    boxShadow: 'var(--shadow-button)'
                  }}>
                    {instruction.step}
                  </div>

                  {/* Content */}
                  <div style={{ flex: 1 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                      <span style={{ fontSize: '20px' }}>{instruction.icon}</span>
                      <h3 className="elderly-subtitle" style={{ 
                        fontSize: '16px', 
                        margin: '0', 
                        color: 'var(--text-dark-strong)'
                      }}>
                        {instruction.title}
                      </h3>
                    </div>
                    
                    <p className="elderly-text" style={{ 
                      fontSize: '14px', 
                      margin: '0 0 12px 0', 
                      color: 'var(--text-dark)',
                      lineHeight: '1.4'
                    }}>
                      {instruction.description}
                    </p>

                    {/* Copyable VPN Key */}
                    {instruction.copyable && (
                      <div style={{ 
                        background: 'var(--bg-card)', 
                        border: '1px solid var(--border-light)', 
                        borderRadius: '8px', 
                        padding: '12px',
                        position: 'relative'
                      }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                          <div style={{ flex: 1, minWidth: 0 }}>
                            <p style={{ 
                              fontSize: '12px', 
                              margin: '0 0 8px 0', 
                              color: 'var(--text-dark-secondary)',
                              fontWeight: '500'
                            }}>
                              VPN ключ для копирования:
                            </p>
                            <pre style={{ 
                              fontSize: '12px', 
                              color: '#10b981', 
                              margin: '0',
                              fontFamily: 'monospace',
                              whiteSpace: 'pre-wrap',
                              wordBreak: 'break-all',
                              background: 'transparent',
                              border: 'none',
                              padding: '0'
                            }}>
                              {instruction.copyable}
                            </pre>
                          </div>
                          <button
                            onClick={() => copyToClipboard(instruction.copyable, instruction.step)}
                            style={{
                              background: 'var(--gradient-yellow)',
                              border: 'none',
                              borderRadius: '8px',
                              padding: '8px',
                              cursor: 'pointer',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              marginLeft: '12px',
                              transition: 'all 0.2s ease',
                              boxShadow: 'var(--shadow-button)'
                            }}
                            onMouseEnter={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                            onMouseLeave={(e) => e.currentTarget.style.transform = 'scale(1)'}
                          >
                            {copiedStep === instruction.step ? (
                              <Check className="w-4 h-4" style={{ color: 'var(--text-dark)' }} />
                            ) : (
                              <Copy className="w-4 h-4" style={{ color: 'var(--text-dark)' }} />
                            )}
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Help Text */}
          <div style={{ 
            marginTop: '24px', 
            padding: '16px', 
            background: 'var(--bg-card-glass)', 
            borderRadius: '12px', 
            border: '1px solid var(--border-light)',
            textAlign: 'center'
          }}>
            <p className="elderly-text" style={{ 
              fontSize: '14px', 
              margin: '0', 
              color: 'var(--text-dark-secondary)',
              lineHeight: '1.4'
            }}>
              Следуйте инструкциям по порядку. После добавления конфигурации нажмите "Далее" для завершения настройки.
            </p>
          </div>
        </div>

        {/* Next Button */}
        <button
          onClick={() => {
            if (webApp && isTelegramMode) {
              webApp.HapticFeedback.impactOccurred('light');
            }
            // Переходим к странице поздравления
            window.location.hash = '#vpn-success';
          }}
          className="vpn-button vpn-button-primary"
          style={{ marginTop: '20px' }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <span>Далее</span>
            <ExternalLink className="w-5 h-5" />
          </div>
        </button>
      </div>
    </Layout>
  );
};

export default ManualAddPage;

