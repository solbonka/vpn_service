import React from 'react';
import { ArrowLeft } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

const SubscriptionsPage: React.FC = () => {
  const { user, webApp, isTelegramMode } = useTelegram();

  return (
    <Layout>
      <div className="py-6">
        <button 
          onClick={() => window.location.hash = ''} 
          className="mb-4 text-blue-500 font-medium flex items-center gap-2"
        >
          <ArrowLeft className="w-4 h-4" />
          Back to Home
        </button>
        
        <h2 className="text-2xl font-bold mb-6 text-center">Extend Subscription</h2>
        
        <div className="text-center py-8">
          <div className="bg-yellow-100 p-4 rounded-lg">
            <h3 className="font-semibold text-yellow-800 mb-2">Coming Soon</h3>
            <p className="text-yellow-700">
              Subscription extension functionality will be available soon.
            </p>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default SubscriptionsPage;