import React, { createContext, useContext, useState, ReactNode } from 'react';

interface ChartsContextType {
    period: string;
    startDate: string;
    endDate: string;
    setPeriod: (period: string) => void;
    setStartDate: (date: string) => void;
    setEndDate: (date: string) => void;
    resetToDefault: () => void;
}

const ChartsContext = createContext<ChartsContextType | undefined>(undefined);

interface ChartsProviderProps {
    children: ReactNode;
}

export const ChartsProvider: React.FC<ChartsProviderProps> = ({ children }) => {
    const [period, setPeriod] = useState('7d');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');

    const resetToDefault = () => {
        setPeriod('7d');
        setStartDate('');
        setEndDate('');
    };

    const value: ChartsContextType = {
        period,
        startDate,
        endDate,
        setPeriod,
        setStartDate,
        setEndDate,
        resetToDefault
    };

    return (
        <ChartsContext.Provider value={value}>
            {children}
        </ChartsContext.Provider>
    );
};

export const useChartsContext = (): ChartsContextType => {
    const context = useContext(ChartsContext);
    if (context === undefined) {
        throw new Error('useChartsContext must be used within a ChartsProvider');
    }
    return context;
};
