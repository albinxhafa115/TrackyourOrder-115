import express from 'express'
import cors from 'cors'
import helmet from 'helmet'
import dotenv from 'dotenv'
import rateLimit from 'express-rate-limit'
import { createServer } from 'http'
import { Server } from 'socket.io'
import cron from 'node-cron'

// Import routes
import authRoutes from './routes/auth.js'
import courierRoutes from './routes/courier.js'
import deliveryRoutes from './routes/delivery.js'
import gpsRoutes from './routes/gps.js'
import trackingRoutes from './routes/tracking.js'

// Import middleware
import { errorHandler } from './middleware/errorHandler.js'

// Import database
import pool, { query } from './config/database.js'

// Load environment variables
dotenv.config()

// Create Express app
const app = express()
const httpServer = createServer(app)

// Initialize Socket.IO for real-time tracking
const io = new Server(httpServer, {
  cors: {
    origin: [
      'http://localhost:3000',
      'http://192.168.1.4:3000',
      'https://*.ngrok-free.app',
      'https://*.ngrok.io'
    ],
    methods: ['GET', 'POST'],
  },
})

// ============================================================================
// MIDDLEWARE
// ============================================================================

// Security middleware
app.use(helmet())

// CORS configuration
app.use(
  cors({
    origin: [
      'http://localhost:3000',
      'http://192.168.1.4:3000',
      'https://*.ngrok-free.app',
      'https://*.ngrok.io'
    ],
    credentials: true,
  })
)

// Body parsers
app.use(express.json({ limit: '10mb' }))
app.use(express.urlencoded({ extended: true, limit: '10mb' }))

// Rate limiting
const limiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000, // 15 minutes
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100,
  message: 'Too many requests from this IP, please try again later.',
})

app.use('/api/', limiter)

// Request logging (development only)
if (process.env.NODE_ENV === 'development') {
  app.use((req, res, next) => {
    console.log(`${req.method} ${req.path}`)
    next()
  })
}

// ============================================================================
// ROUTES
// ============================================================================

app.get('/', (req, res) => {
  res.json({
    message: 'Kuosht GPS Tracking API',
    version: '1.0.0',
    status: 'online',
  })
})

app.get('/health', async (req, res) => {
  try {
    await pool.query('SELECT 1')
    res.json({ status: 'healthy', database: 'connected' })
  } catch (error) {
    res.status(503).json({ status: 'unhealthy', database: 'disconnected' })
  }
})

// API routes
app.use('/api/auth', authRoutes)
app.use('/api/courier', courierRoutes)
app.use('/api/delivery', deliveryRoutes)
app.use('/api/gps', gpsRoutes)
app.use('/api/track', trackingRoutes)

// 404 handler
app.use((req, res) => {
  res.status(404).json({ message: 'Route not found' })
})

// Error handler (must be last)
app.use(errorHandler)

// ============================================================================
// SOCKET.IO - REAL-TIME TRACKING
// ============================================================================

io.on('connection', (socket) => {
  console.log('Client connected:', socket.id)

  // Join order room for real-time updates
  socket.on('track_order', (orderNumber) => {
    socket.join(`order_${orderNumber}`)
    console.log(`Client tracking order: ${orderNumber}`)
  })

  // Leave order room
  socket.on('stop_tracking', (orderNumber) => {
    socket.leave(`order_${orderNumber}`)
  })

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id)
  })
})

// Export io for use in other modules
export { io }

// ============================================================================
// CRON JOBS
// ============================================================================

// Cleanup old tracking data (runs daily at 2 AM)
cron.schedule('0 2 * * *', async () => {
  try {
    console.log('Running cleanup job: Deleting old tracking data...')
    const result = await query(
      "DELETE FROM tracking_data WHERE created_at < NOW() - INTERVAL '24 hours'"
    )
    console.log(`Cleanup completed: ${result.rowCount} records deleted`)
  } catch (error) {
    console.error('Cleanup job failed:', error)
  }
})

// ============================================================================
// START SERVER
// ============================================================================

const PORT = process.env.PORT || 5000

httpServer.listen(PORT, () => {
  console.log('============================================')
  console.log(`🚀 Server running on port ${PORT}`)
  console.log(`📍 Environment: ${process.env.NODE_ENV}`)
  console.log(`🌐 API URL: http://localhost:${PORT}`)
  console.log('============================================')
})

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully...')
  httpServer.close(() => {
    console.log('Server closed')
    pool.end(() => {
      console.log('Database pool closed')
      process.exit(0)
    })
  })
})

export default app
