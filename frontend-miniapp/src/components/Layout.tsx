import React, { ReactNode } from 'react';

interface LayoutProps {
  children: ReactNode;
  className?: string;
}

const Layout: React.FC<LayoutProps> = ({ children, className = '' }) => {
  return (
    <div 
      className={`min-h-screen ${className}`}
      style={{ 
        background: 'var(--gradient-primary)', 
        color: 'var(--text-primary)',
        fontFamily: 'system-ui, -apple-system, sans-serif',
        position: 'relative'
      }}
    >
      {/* Тонкие декоративные элементы */}
      <div style={{
        position: 'absolute',
        top: '0',
        left: '0',
        right: '0',
        bottom: '0',
        background: 'radial-gradient(circle at 50% 30%, rgba(251, 191, 36, 0.05) 0%, transparent 60%)',
        pointerEvents: 'none'
      }} />
      
      <div className="container mx-auto px-4 pb-6" style={{ position: 'relative', zIndex: 1 }}>
        {children}
      </div>
    </div>
  );
};

export default Layout;