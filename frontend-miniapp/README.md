# VPN Telegram Mini App

A React-based Telegram Mini App for VPN service management with subscription handling and server connection capabilities.

## Features

- 🔐 VPN server connection and management
- 💳 Subscription purchase and management  
- 📊 Real-time connection statistics
- 🎨 Telegram-native UI/UX design
- 📱 Fully responsive mobile interface
- ⚡ Telegram WebApp API integration

## Technology Stack

- React 18 with TypeScript
- Vite for fast development and building
- Tailwind CSS for styling
- Telegram WebApp SDK
- Lucide React for icons
- Axios for API communication

## Project Structure

```
frontend-miniapp/
├── src/
│   ├── components/          # Reusable UI components
│   ├── hooks/              # Custom React hooks
│   ├── pages/              # Page components
│   ├── services/           # API services
│   ├── types/              # TypeScript type definitions
│   └── main.tsx            # Application entry point
├── public/                 # Static assets
├── Dockerfile             # Docker configuration
├── nginx.conf             # Nginx configuration
└── package.json           # Project dependencies
```

## Development

1. Install dependencies:
```bash
npm install
```

2. Start development server:
```bash
npm run dev
```

3. Build for production:
```bash
npm run build
```

## Docker Deployment

The app is configured to run in a Docker container with Nginx:

```bash
docker build -t vpn-miniapp .
docker run -p 80:80 vpn-miniapp
```

## Telegram Integration

This app integrates with Telegram's WebApp API providing:
- User authentication via Telegram
- Native UI theming
- Haptic feedback
- Main/Back button integration
- Secure data transfer

## API Integration

The app communicates with your Laravel backend through:
- RESTful API endpoints
- Telegram authentication headers
- Real-time status updates
- Payment processing integration

## Environment Variables

Create a `.env` file based on `.env.example`:
- `VITE_API_BASE_URL` - Backend API URL
- `VITE_TELEGRAM_BOT_USERNAME` - Your bot username  
- `VITE_ENVIRONMENT` - Environment (development/production)