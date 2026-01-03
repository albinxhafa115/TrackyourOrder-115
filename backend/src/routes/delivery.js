import express from 'express'
import { authenticate } from '../middleware/auth.js'
import { query, transaction } from '../config/database.js'

const router = express.Router()

/**
 * POST /api/delivery/start
 * Start delivery for selected orders
 */
router.post('/start', authenticate, async (req, res, next) => {
  try {
    const { courier_id, order_ids } = req.body

    if (!order_ids || !Array.isArray(order_ids) || order_ids.length === 0) {
      return res.status(400).json({ message: 'Order IDs are required' })
    }

    await transaction(async (client) => {
      // Update orders status
      await client.query(
        `UPDATE orders SET
          courier_id = $1,
          status = 'out_for_delivery',
          assigned_at = CURRENT_TIMESTAMP
         WHERE id = ANY($2)`,
        [courier_id, order_ids]
      )

      // Create delivery events
      for (const orderId of order_ids) {
        await client.query(
          `INSERT INTO delivery_events (order_id, courier_id, event_type, description)
           VALUES ($1, $2, 'started', 'Delivery started')`,
          [orderId, courier_id]
        )
      }
    })

    res.json({ success: true, message: 'Delivery started successfully' })
  } catch (error) {
    next(error)
  }
})

/**
 * POST /api/delivery/complete/:orderId
 * Mark delivery as completed
 */
router.post('/complete/:orderId', authenticate, async (req, res, next) => {
  try {
    const { orderId } = req.params
    const { status, completion_type, notes, photo, signature } = req.body

    await transaction(async (client) => {
      // Update order
      await client.query(
        `UPDATE orders SET
          status = $1,
          completion_type = $2,
          completed_at = CURRENT_TIMESTAMP
         WHERE id = $3 AND courier_id = $4`,
        [status || 'delivered', completion_type, orderId, req.courier.id]
      )

      // Create event
      await client.query(
        `INSERT INTO delivery_events (order_id, courier_id, event_type, description, event_data)
         VALUES ($1, $2, 'delivered', $3, $4)`,
        [
          orderId,
          req.courier.id,
          notes || 'Order delivered successfully',
          JSON.stringify({ completion_type, photo, signature }),
        ]
      )
    })

    res.json({ success: true, message: 'Delivery completed successfully' })
  } catch (error) {
    next(error)
  }
})

/**
 * POST /api/delivery/cancel/:orderId
 * Cancel delivery
 */
router.post('/cancel/:orderId', authenticate, async (req, res, next) => {
  try {
    const { orderId } = req.params
    const { reason, notes, photo } = req.body

    if (!reason) {
      return res.status(400).json({ message: 'Cancellation reason is required' })
    }

    await transaction(async (client) => {
      // Update order status
      await client.query(
        `UPDATE orders SET status = 'cancelled'
         WHERE id = $1 AND courier_id = $2`,
        [orderId, req.courier.id]
      )

      // Create event
      await client.query(
        `INSERT INTO delivery_events (order_id, courier_id, event_type, description, event_data)
         VALUES ($1, $2, 'cancelled', $3, $4)`,
        [orderId, req.courier.id, notes || reason, JSON.stringify({ reason, photo })]
      )
    })

    res.json({ success: true, message: 'Delivery cancelled successfully' })
  } catch (error) {
    next(error)
  }
})

/**
 * POST /api/delivery/reschedule/:orderId
 * Reschedule delivery
 */
router.post('/reschedule/:orderId', authenticate, async (req, res, next) => {
  try {
    const { orderId } = req.params
    const { new_date, reason, notes, preferred_time } = req.body

    if (!new_date) {
      return res.status(400).json({ message: 'New delivery date is required' })
    }

    await transaction(async (client) => {
      // Get current scheduled date
      const orderResult = await client.query(
        'SELECT scheduled_date FROM orders WHERE id = $1 AND courier_id = $2',
        [orderId, req.courier.id]
      )

      if (orderResult.rows.length === 0) {
        throw new Error('Order not found or not assigned to this courier')
      }

      const originalDate = orderResult.rows[0].scheduled_date

      // Update order
      await client.query(
        `UPDATE orders SET
          status = 'rescheduled',
          scheduled_date = $1,
          scheduled_time_slot = $2
         WHERE id = $3`,
        [new_date, preferred_time, orderId]
      )

      // Create reschedule record
      await client.query(
        `INSERT INTO reschedules (order_id, courier_id, original_date, new_date, reason, preferred_time, notes)
         VALUES ($1, $2, $3, $4, $5, $6, $7)`,
        [orderId, req.courier.id, originalDate, new_date, reason, preferred_time, notes]
      )

      // Create event
      await client.query(
        `INSERT INTO delivery_events (order_id, courier_id, event_type, description, event_data)
         VALUES ($1, $2, 'rescheduled', $3, $4)`,
        [
          orderId,
          req.courier.id,
          `Rescheduled to ${new_date}`,
          JSON.stringify({ reason, new_date, preferred_time }),
        ]
      )
    })

    res.json({ success: true, message: 'Delivery rescheduled successfully' })
  } catch (error) {
    next(error)
  }
})

export default router
