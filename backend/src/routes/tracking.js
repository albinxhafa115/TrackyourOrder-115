import express from 'express'
import { query } from '../config/database.js'

const router = express.Router()

/**
 * GET /api/track/:orderNumber
 * Public endpoint for customers to track their order
 */
router.get('/:orderNumber', async (req, res, next) => {
  try {
    const { orderNumber } = req.params

    // Get order details
    const orderResult = await query(
      `SELECT
        id,
        order_number,
        customer_name,
        customer_phone,
        delivery_address,
        delivery_lat,
        delivery_lng,
        status,
        scheduled_date,
        scheduled_time_slot,
        courier_id,
        completed_at,
        created_at
       FROM orders
       WHERE order_number = $1`,
      [orderNumber]
    )

    if (orderResult.rows.length === 0) {
      return res.status(404).json({ message: 'Order not found' })
    }

    const order = orderResult.rows[0]

    // Get current courier position (if delivery is active)
    let current_position = null
    if (order.status === 'out_for_delivery' && order.courier_id) {
      const posResult = await query(
        `SELECT lat, lng, speed, accuracy, timestamp, created_at
         FROM tracking_data
         WHERE courier_id = $1
         ORDER BY created_at DESC
         LIMIT 1`,
        [order.courier_id]
      )

      if (posResult.rows.length > 0) {
        current_position = posResult.rows[0]
      }
    }

    // Calculate ETA if courier position is available
    let eta = null
    if (current_position) {
      const distance = calculateDistance(
        current_position.lat,
        current_position.lng,
        order.delivery_lat,
        order.delivery_lng
      )

      // Assume average speed of 30 km/h + 5 min buffer
      const timeMinutes = Math.ceil((distance / 30) * 60) + 5

      eta = {
        distance_km: distance.toFixed(2),
        minutes: timeMinutes,
        time_string: formatTime(timeMinutes),
        arrival_time: getArrivalTime(timeMinutes),
      }
    }

    // Get delivery events timeline
    const eventsResult = await query(
      `SELECT
        event_type,
        description,
        event_data,
        created_at
       FROM delivery_events
       WHERE order_id = $1
       ORDER BY created_at DESC`,
      [order.id]
    )

    // Build response
    const response = {
      order: {
        order_number: order.order_number,
        customer_name: order.customer_name,
        delivery_address: order.delivery_address,
        delivery_lat: order.delivery_lat,
        delivery_lng: order.delivery_lng,
        status: order.status,
        scheduled_date: order.scheduled_date,
        scheduled_time_slot: order.scheduled_time_slot,
        completed_at: order.completed_at,
        created_at: order.created_at,
      },
      current_position,
      eta,
      events: eventsResult.rows,
    }

    res.json(response)
  } catch (error) {
    next(error)
  }
})

/**
 * GET /api/track/:orderNumber/route
 * Get route history for an order
 */
router.get('/:orderNumber/route', async (req, res, next) => {
  try {
    const { orderNumber } = req.params

    // Get order
    const orderResult = await query(
      'SELECT id, courier_id FROM orders WHERE order_number = $1',
      [orderNumber]
    )

    if (orderResult.rows.length === 0) {
      return res.status(404).json({ message: 'Order not found' })
    }

    const order = orderResult.rows[0]

    if (!order.courier_id) {
      return res.json({ route: [] })
    }

    // Get tracking data for this courier (last 24 hours)
    const routeResult = await query(
      `SELECT lat, lng, speed, timestamp, created_at
       FROM tracking_data
       WHERE courier_id = $1
       AND created_at > NOW() - INTERVAL '24 hours'
       ORDER BY created_at ASC`,
      [order.courier_id]
    )

    res.json({ route: routeResult.rows })
  } catch (error) {
    next(error)
  }
})

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Calculate distance between two coordinates using Haversine formula
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

/**
 * Format time in minutes to human-readable string
 */
function formatTime(minutes) {
  if (minutes < 1) return 'Më pak se 1 min'
  if (minutes < 60) return `~${minutes} min`

  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60

  if (mins === 0) return `~${hours}h`
  return `~${hours}h ${mins}min`
}

/**
 * Get estimated arrival time
 */
function getArrivalTime(minutes) {
  const now = new Date()
  const arrival = new Date(now.getTime() + minutes * 60000)

  return arrival.toLocaleTimeString('sq-AL', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

export default router
