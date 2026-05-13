import Header from './Header/Header'
import React from 'react'

interface LayoutProps {
    children: React.ReactNode;
}

function Layout({children}: LayoutProps) {
    return (
        <>
            <header>
                <Header/>
            </header>
            <main>
                {children}
            </main>
        </>
    )
}

export default Layout