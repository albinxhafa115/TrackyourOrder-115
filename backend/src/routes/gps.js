import express from 'express'
import { authenticate } from '../middleware/auth.js'
import { query } from '../config/database.js'

const router = express.Router()

/**
 * POST /api/gps/update
 * Update GPS position for courier
 */
router.post('/update', authenticate, async (req, res, next) => {
  try {
    const { device_id, lat, lng, accuracy, speed, heading, battery, timestamp } = req.body
    const numericLat = Number(lat)
    const numericLng = Number(lng)

    // Validate required fields
    if (!Number.isFinite(numericLat) || !Number.isFinite(numericLng) || !timestamp) {
      return res.status(400).json({ message: 'Latitude, longitude, and timestamp are required' })
    }

    // Insert GPS data
    await query(
      `INSERT INTO tracking_data
       (courier_id, device_id, lat, lng, accuracy, speed, heading, battery, timestamp)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)`,
      [
        req.courier.id,
        device_id || `courier_${req.courier.id}`,
        numericLat,
        numericLng,
        accuracy || null,
        speed || 0,
        heading || null,
        battery || null,
        timestamp,
      ]
    )

    // Find active orders for this courier
    const activeOrders = await query(
      `SELECT id, order_number, delivery_lat, delivery_lng
       FROM orders
       WHERE courier_id = $1 AND status = 'out_for_delivery'`,
      [req.courier.id]
    )

    // Check proximity to each order and emit Socket.IO events
    // Note: Socket.IO instance needs to be imported from server.js
    // For now, we'll just log the update
    for (const order of activeOrders.rows) {
      const distance = calculateDistance(lat, lng, order.delivery_lat, order.delivery_lng)

      console.log(`Courier ${req.courier.id} is ${distance.toFixed(2)}km from order ${order.order_number}`)

      // If within 4km, could trigger auto-call or notification
      if (distance <= 4) {
        console.log(`🔔 Courier approaching order ${order.order_number} - ${distance.toFixed(2)}km away`)
      }

      // Emit Socket.IO event (if io is available)
      // io.to(`order_${order.order_number}`).emit('location_update', { lat, lng, timestamp, speed })
    }

    res.json({
      success: true,
      active_orders: activeOrders.rows.length,
    })
  } catch (error) {
    next(error)
  }
})

/**
 * GET /api/gps/location/:courierId
 * Get latest location for a courier (admin/debugging)
 */
router.get('/location/:courierId', authenticate, async (req, res, next) => {
  try {
    const { courierId } = req.params

    const result = await query(
      `SELECT lat, lng, speed, accuracy, battery, timestamp, created_at
       FROM tracking_data
       WHERE courier_id = $1
       ORDER BY created_at DESC
       LIMIT 1`,
      [courierId]
    )

    if (result.rows.length === 0) {
      return res.status(404).json({ message: 'No location data found' })
    }

    res.json(result.rows[0])
  } catch (error) {
    next(error)
  }
})

/**
 * Helper: Calculate distance between two coordinates (Haversine formula)
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371 // Earth's radius in km
  const dLat = toRad(lat2 - lat1)
  const dLon = toRad(lon2 - lon1)

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2)

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  const distance = R * c

  return distance
}

function toRad(deg) {
  return (deg * Math.PI) / 180
}

export default router
