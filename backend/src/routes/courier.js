import express from 'express'
import { authenticate } from '../middleware/auth.js'
import { query } from '../config/database.js'

const router = express.Router()

/**
 * GET /api/courier/orders/today
 * Get today's orders for authenticated courier
 */
router.get('/orders/today', authenticate, async (req, res, next) => {
  try {
    const result = await query(
      `SELECT
        id,
        order_number,
        customer_name,
        customer_phone,
        customer_email,
        delivery_address,
        delivery_lat,
        delivery_lng,
        status,
        order_value,
        payment_method,
        scheduled_date,
        scheduled_time_slot,
        delivery_notes
       FROM orders
       WHERE scheduled_date = CURRENT_DATE
       AND status IN ('pending', 'confirmed')
       AND (courier_id IS NULL OR courier_id = $1)
       ORDER BY id`,
      [req.courier.id]
    )

    // Calculate approximate distance from a reference point (e.g., office location)
    // For demo, we'll add mock distance - in production, use actual courier location
    const orders = result.rows.map((order) => ({
      ...order,
      distance_km: (Math.random() * 10 + 1).toFixed(2), // Mock distance for demo
    }))

    res.json({ orders })
  } catch (error) {
    next(error)
  }
})

/**
 * GET /api/courier/stats
 * Get courier statistics
 */
router.get('/stats', authenticate, async (req, res, next) => {
  try {
    const result = await query(
      `SELECT
        COUNT(*) FILTER (WHERE status = 'delivered') as delivered_count,
        COUNT(*) FILTER (WHERE status = 'cancelled') as cancelled_count,
        COUNT(*) FILTER (WHERE status = 'out_for_delivery') as active_count,
        COUNT(*) as total_count
       FROM orders
       WHERE courier_id = $1
       AND DATE(created_at) = CURRENT_DATE`,
      [req.courier.id]
    )

    res.json(result.rows[0])
  } catch (error) {
    next(error)
  }
})

export default router
