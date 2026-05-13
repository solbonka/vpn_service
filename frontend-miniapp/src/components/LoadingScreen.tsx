import React from 'react';
import { Loader } from 'lucide-react';

const LoadingScreen: React.FC = () => {
  return (
    <div className="min-h-screen bg-tg-bg flex items-center justify-center">
      <div className="text-center">
        <Loader className="w-8 h-8 animate-spin mx-auto mb-4 text-tg-blue" />
        <p className="text-tg-hint">Loading...</p>
      </div>
    </div>
  );
};

export default LoadingScreen;