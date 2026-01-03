# KUOSHT GPS Tracking System

100% Web-Based Progressive Web App (PWA) for GPS tracking in e-commerce deliveries.

## Project Overview

- **Type**: Full-stack web application (NO mobile apps)
- **Frontend**: React + Vite + PWA
- **Backend**: Node.js + Express + PostgreSQL
- **Maps**: Leaflet + OpenStreetMap
- **GPS**: HTML5 Geolocation API (browser native)

## Cost & Timeline

| Item | Amount |
|------|--------|
| **Development Cost** | €4,100-6,900 |
| **Timeline** | 10-12 weeks |
| **Savings vs Native Apps** | €2,900-5,100 |

## Features

### For Couriers (`/courier`)
- Login via mobile browser
- Dashboard with today's orders
- GPS tracking (HTML5 Geolocation)
- Navigation integration (Google Maps/Waze)
- Auto-call functionality
- Delivery completion (delivered/cancelled/reschedule)
- Offline support with background sync
- PWA - Add to Home Screen

### For Customers (`/track/:orderNumber`)
- Live GPS tracking map
- Real-time ETA updates
- Status timeline
- Auto-refresh every 30s
- Works on any device

## Project Structure

```
TrackyourOrder-115/
├── frontend/                 # React + Vite PWA
│   ├── src/
│   │   ├── components/
│   │   │   ├── courier/      # Courier interface
│   │   │   │   ├── Login.jsx
│   │   │   │   ├── Dashboard.jsx ✓
│   │   │   │   ├── ActiveDelivery.jsx
│   │   │   │   ├── DeliveryCompletion.jsx
│   │   │   │   └── RescheduleForm.jsx
│   │   │   ├── customer/     # Customer tracking
│   │   │   │   ├── TrackingPage.jsx
│   │   │   │   ├── LiveMap.jsx
│   │   │   │   └── StatusTimeline.jsx
│   │   │   ├── shared/       # Shared components
│   │   │   │   ├── Map.jsx
│   │   │   │   └── Navigation.jsx
│   │   │   └── admin/        # Admin dashboard (bonus)
│   │   │       └── Dashboard.jsx
│   │   ├── hooks/
│   │   │   └── useGeolocation.js ✓
│   │   ├── services/
│   │   │   ├── api.js ✓
│   │   │   ├── gps.js ✓
│   │   │   └── storage.js ✓
│   │   ├── store/
│   │   │   ├── store.js ✓
│   │   │   └── slices/
│   │   │       ├── authSlice.js ✓
│   │   │       ├── ordersSlice.js ✓
│   │   │       ├── trackingSlice.js ✓
│   │   │       └── deliverySlice.js ✓
│   │   ├── utils/
│   │   │   ├── distance.js ✓
│   │   │   ├── eta.js ✓
│   │   │   └── navigation.js ✓
│   │   ├── App.jsx ✓
│   │   ├── main.jsx ✓
│   │   └── index.css ✓
│   ├── public/
│   │   └── icons/            # PWA icons
│   ├── package.json ✓
│   ├── vite.config.js ✓
│   ├── tailwind.config.js ✓
│   └── postcss.config.js ✓
│
├── backend/                  # Node.js + Express API
│   ├── src/
│   │   ├── config/
│   │   │   └── database.js
│   │   ├── middleware/
│   │   │   ├── auth.js
│   │   │   └── errorHandler.js
│   │   ├── routes/
│   │   │   ├── auth.js
│   │   │   ├── courier.js
│   │   │   ├── delivery.js
│   │   │   ├── gps.js
│   │   │   └── tracking.js
│   │   ├── controllers/
│   │   │   ├── authController.js
│   │   │   ├── courierController.js
│   │   │   ├── deliveryController.js
│   │   │   └── gpsController.js
│   │   ├── models/
│   │   │   └── (using raw SQL queries)
│   │   ├── services/
│   │   │   ├── emailService.js
│   │   │   ├── smsService.js
│   │   │   └── etaService.js
│   │   ├── utils/
│   │   │   └── distance.js
│   │   └── server.js
│   ├── migrations/
│   │   └── 001_initial_schema.sql
│   ├── package.json
│   └── .env.example
│
├── docs/                     # Documentation
│   └── GPS_Tracking_WEB_ONLY_Final.pdf ✓
│
├── docker-compose.yml
├── .gitignore
└── README.md ✓
```

## Installation & Setup

### Prerequisites
- Node.js 18+ and npm
- PostgreSQL 14+
- Git

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

Frontend runs on: http://localhost:3000

### Backend Setup

```bash
cd backend
npm install

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Run migrations
npm run migrate

# Start server
npm run dev
```

Backend runs on: http://localhost:5000

### Database Setup

```sql
-- Create database
CREATE DATABASE kuosht_tracking;

-- Run migrations
psql -U postgres -d kuosht_tracking -f backend/migrations/001_initial_schema.sql
```

## Environment Variables

### Frontend (`.env`)
```
VITE_API_URL=http://localhost:5000/api
VITE_MAPS_API_KEY=your_google_maps_api_key (optional)
```

### Backend (`.env`)
```
PORT=5000
NODE_ENV=development

# Database
DB_HOST=localhost
DB_PORT=5432
DB_NAME=kuosht_tracking
DB_USER=postgres
DB_PASSWORD=your_password

# JWT
JWT_SECRET=your_secret_key_here
JWT_EXPIRE=8h

# Email (SendGrid)
SENDGRID_API_KEY=your_sendgrid_api_key
FROM_EMAIL=noreply@kuosht.com

# SMS (Twilio)
TWILIO_ACCOUNT_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_token
TWILIO_PHONE_NUMBER=+38344xxxxxx

# Maps
GOOGLE_MAPS_API_KEY=your_google_maps_api_key (optional)
```

## Database Schema

### Tables:
- `couriers` - Courier accounts
- `customers` - Customer information
- `orders` - Delivery orders
- `tracking_data` - GPS coordinates (24h retention)
- `delivery_events` - Status change events
- `reschedules` - Rescheduled deliveries

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login courier

### Courier
- `GET /api/courier/orders/today` - Get today's orders

### Delivery
- `POST /api/delivery/start` - Start delivery
- `POST /api/delivery/complete/:orderId` - Complete delivery
- `POST /api/delivery/cancel/:orderId` - Cancel delivery
- `POST /api/delivery/reschedule/:orderId` - Reschedule delivery

### GPS
- `POST /api/gps/update` - Update GPS position

### Tracking (Public)
- `GET /api/track/:orderNumber` - Get tracking info

## Testing

### Frontend
```bash
cd frontend
npm run test
npm run test:e2e
```

### Backend
```bash
cd backend
npm run test
```

## Deployment

### Frontend (Vercel)
```bash
cd frontend
vercel deploy --prod
```

### Backend (DigitalOcean/Railway)
```bash
# Build
npm run build

# Start production
npm start
```

### Docker
```bash
docker-compose up -d
```

## PWA Features

- **Offline Support**: Service Workers cache assets
- **Add to Home Screen**: Works like native app
- **Background Sync**: GPS updates sync when online
- **Push Notifications**: (Optional) Delivery updates

## Browser Support

- ✅ Chrome/Edge 90+
- ✅ Safari 14+
- ✅ Firefox 88+
- ❌ IE (not supported)

## Security

- HTTPS only in production
- JWT authentication with 8h expiry
- CORS properly configured
- Input validation & sanitization
- Rate limiting on public endpoints
- SQL injection prevention

## Development Status

### Completed ✓
- Frontend structure & configuration
- Redux store & slices
- GPS tracking hook & services
- Utility functions (distance, ETA, navigation)
- Login component
- Dashboard component
- API service setup
- Offline storage (IndexedDB)

### In Progress 🔄
- Active delivery interface
- Delivery completion flows
- Customer tracking page
- Backend API

### Pending ⏳
- Admin dashboard
- Email/SMS notifications
- Docker setup
- Testing suite
- Documentation

## Support

For issues or questions:
- Create an issue on GitHub
- Contact: support@kuosht.com

## License

Proprietary - © 2025 Kuosht

---

**Version**: 1.0.0
**Last Updated**: January 2, 2026
**Documentation**: See `/docs/GPS_Tracking_WEB_ONLY_Final.pdf`
