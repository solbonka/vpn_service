import React from 'react';
import { Shield, ShieldCheck, Zap, Download, Upload } from 'lucide-react';
import type { ConnectionStatus as ConnectionStatusType } from '../types';

interface ConnectionStatusProps {
  status: ConnectionStatusType;
}

const formatBytes = (bytes: number): string => {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const ConnectionStatus: React.FC<ConnectionStatusProps> = ({ status }) => {
  return (
    <div className="bg-tg-secondary-bg rounded-xl p-6 mb-6 animate-slide-up">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-3">
          {status.connected ? (
            <ShieldCheck className="w-6 h-6 text-green-500" />
          ) : (
            <Shield className="w-6 h-6 text-gray-400" />
          )}
          <div>
            <h3 className="font-semibold text-lg">
              {status.connected ? 'Connected' : 'Disconnected'}
            </h3>
            {status.server && (
              <p className="text-tg-hint text-sm">
                {status.server.city}, {status.server.country}
              </p>
            )}
          </div>
        </div>
        {status.connected && (
          <div className="flex items-center gap-2">
            <div className="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
            <span className="text-sm text-green-500 font-medium">Active</span>
          </div>
        )}
      </div>

      {status.connected && (
        <div className="grid grid-cols-2 gap-4">
          <div className="bg-white bg-opacity-50 rounded-lg p-3">
            <div className="flex items-center gap-2 mb-2">
              <Download className="w-4 h-4 text-tg-blue" />
              <span className="text-sm font-medium">Downloaded</span>
            </div>
            <p className="text-lg font-bold">{formatBytes(status.bytesReceived)}</p>
          </div>
          <div className="bg-white bg-opacity-50 rounded-lg p-3">
            <div className="flex items-center gap-2 mb-2">
              <Upload className="w-4 h-4 text-tg-blue" />
              <span className="text-sm font-medium">Uploaded</span>
            </div>
            <p className="text-lg font-bold">{formatBytes(status.bytesSent)}</p>
          </div>
        </div>
      )}
    </div>
  );
};

export default ConnectionStatus;