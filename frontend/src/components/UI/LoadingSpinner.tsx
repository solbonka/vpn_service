import React from 'react';

interface LoadingSpinnerProps {
    size?: 'sm' | 'md' | 'lg';
    fullscreen?: boolean;
    className?: string;
}

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({
                                                           size = 'md',
                                                           fullscreen = false,
                                                           className = ''
                                                       }) => {
    const sizeClass =
        size === 'sm' ? 'spinner-border-sm' :
        size === 'lg' ? 'spinner-border-lg' : '';

    return (
        <div
            className={`d-flex justify-content-center align-items-center ${className}`}
            style={fullscreen ? {minHeight: "100vh"} : {}}
        >
            <div className={`spinner-border text-primary ${sizeClass}`} role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );
};

export default LoadingSpinner;