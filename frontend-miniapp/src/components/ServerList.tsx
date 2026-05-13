import React from 'react';
import { Wifi, WifiOff, Settings } from 'lucide-react';
import type { VPNServer } from '../types';

interface ServerListProps {
  servers: VPNServer[];
  selectedServer?: VPNServer;
  onServerSelect: (server: VPNServer) => void;
  isConnecting?: boolean;
}

const getLoadColor = (load: number): string => {
  if (load < 30) return 'text-green-500';
  if (load < 70) return 'text-yellow-500';
  return 'text-red-500';
};

const getSignalBars = (ping: number): number => {
  if (ping < 50) return 4;
  if (ping < 100) return 3;
  if (ping < 200) return 2;
  return 1;
};

const SignalBars: React.FC<{ count: number }> = ({ count }) => {
  return (
    <div className="flex items-end gap-1">
      {[1, 2, 3, 4].map((bar) => (
        <div
          key={bar}
          className={`w-1 ${
            bar === 1 ? 'h-2' : bar === 2 ? 'h-3' : bar === 3 ? 'h-4' : 'h-5'
          } ${
            bar <= count ? 'bg-tg-blue' : 'bg-gray-300'
          } rounded-sm`}
        />
      ))}
    </div>
  );
};

const ServerList: React.FC<ServerListProps> = ({
  servers,
  selectedServer,
  onServerSelect,
  isConnecting = false,
}) => {
  return (
    <div className="space-y-3">
      <h3 className="text-lg font-semibold mb-4">Choose Server</h3>
      {servers.map((server) => (
        <div
          key={server.id}
          onClick={() => !isConnecting && onServerSelect(server)}
          className={`p-4 rounded-xl border-2 transition-all duration-200 ${
            selectedServer?.id === server.id
              ? 'border-tg-blue bg-blue-50'
              : 'border-gray-200 bg-white'
          } ${
            isConnecting ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer active:scale-98'
          }`}
        >
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="text-2xl">{server.flag}</div>
              <div className="flex-1">
                <h4 className="font-semibold text-base">{server.name}</h4>
                <p className="text-sm text-tg-hint">{server.city}, {server.country}</p>
              </div>
            </div>
            
            <div className="flex items-center gap-4">
              <div className="text-center">
                <p className={`text-sm font-medium ${getLoadColor(server.load)}`}>
                  {server.load}%
                </p>
                <p className="text-xs text-tg-hint">Load</p>
              </div>
              
              <div className="text-center">
                <SignalBars count={getSignalBars(server.ping)} />
                <p className="text-xs text-tg-hint mt-1">{server.ping}ms</p>
              </div>

              <div className="flex items-center">
                {server.status === 'online' ? (
                  <Wifi className="w-5 h-5 text-green-500" />
                ) : server.status === 'maintenance' ? (
                  <Settings className="w-5 h-5 text-yellow-500" />
                ) : (
                  <WifiOff className="w-5 h-5 text-red-500" />
                )}
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ServerList;