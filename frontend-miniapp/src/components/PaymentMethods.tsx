import React from 'react';
import { CreditCard } from 'lucide-react';
import type { PaymentMethod } from '../types';

interface PaymentMethodsProps {
  methods: PaymentMethod[];
  selectedMethod?: PaymentMethod;
  onMethodSelect: (method: PaymentMethod) => void;
}

const PaymentMethods: React.FC<PaymentMethodsProps> = ({
  methods,
  selectedMethod,
  onMethodSelect,
}) => {
  return (
    <div className="space-y-3">
      <h3 className="text-lg font-semibold mb-4">Payment Method</h3>
      
      <div className="grid grid-cols-2 gap-3">
        {methods.map((method) => (
          <div
            key={method.id}
            onClick={() => onMethodSelect(method)}
            className={`p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 active:scale-95 ${
              selectedMethod?.id === method.id
                ? 'border-tg-blue bg-blue-50'
                : 'border-gray-200 bg-white hover:border-gray-300'
            }`}
          >
            <div className="text-center">
              <div className="mb-2">
                {method.icon ? (
                  <img src={method.icon} alt={method.name} className="w-8 h-8 mx-auto" />
                ) : (
                  <CreditCard className="w-8 h-8 mx-auto text-tg-blue" />
                )}
              </div>
              <h4 className="font-medium text-sm">{method.name}</h4>
              <p className="text-xs text-tg-hint">{method.currency}</p>
            </div>
          </div>
        ))}
      </div>
      
      {selectedMethod && (
        <div className="mt-4 p-4 bg-tg-secondary-bg rounded-xl">
          <p className="text-sm text-tg-hint">
            Minimum payment: {selectedMethod.minAmount} {selectedMethod.currency}
          </p>
        </div>
      )}
    </div>
  );
};

export default PaymentMethods;