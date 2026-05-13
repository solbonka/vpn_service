import React from 'react';
import { Star, Check } from 'lucide-react';
import type { Subscription } from '../types';

interface SubscriptionCardProps {
  subscription: Subscription;
  onSelect: (subscription: Subscription) => void;
  selected?: boolean;
}

const SubscriptionCard: React.FC<SubscriptionCardProps> = ({
  subscription,
  onSelect,
  selected = false,
}) => {
  return (
    <div
      onClick={() => onSelect(subscription)}
      className={`relative p-6 rounded-xl border-2 cursor-pointer transition-all duration-200 active:scale-95 ${
        selected
          ? 'border-tg-blue bg-blue-50'
          : 'border-gray-200 bg-white hover:border-gray-300'
      }`}
    >
      {subscription.popular && (
        <div className="absolute -top-2 left-1/2 transform -translate-x-1/2">
          <div className="bg-tg-blue text-white px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1">
            <Star className="w-3 h-3" />
            Popular
          </div>
        </div>
      )}

      <div className="text-center mb-4">
        <h3 className="text-xl font-bold mb-2">{subscription.name}</h3>
        <div className="mb-2">
          <span className="text-3xl font-bold">{subscription.price}</span>
          <span className="text-tg-hint ml-1">{subscription.currency}</span>
        </div>
        <p className="text-tg-hint text-sm">
          {subscription.duration} days • {subscription.traffic}GB
        </p>
      </div>

      <div className="space-y-2 mb-6">
        {subscription.features.map((feature, index) => (
          <div key={index} className="flex items-center gap-2">
            <Check className="w-4 h-4 text-green-500 flex-shrink-0" />
            <span className="text-sm">{feature}</span>
          </div>
        ))}
      </div>

      <button
        className={`w-full py-3 rounded-lg font-semibold transition-colors ${
          selected
            ? 'bg-tg-blue text-white'
            : 'bg-tg-secondary-bg text-tg-text hover:bg-gray-300'
        }`}
      >
        {selected ? 'Selected' : 'Select Plan'}
      </button>
    </div>
  );
};

export default SubscriptionCard;