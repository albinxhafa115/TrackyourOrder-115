import jwt from 'jsonwebtoken'
import { query } from '../config/database.js'

/**
 * Middleware to verify JWT token and authenticate requests
 */
export const authenticate = async (req, res, next) => {
  try {
    // Get token from header
    const authHeader = req.headers.authorization

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({ message: 'No token provided' })
    }

    const token = authHeader.split(' ')[1]

    // Verify token
    const decoded = jwt.verify(token, process.env.JWT_SECRET)

    // Get courier from database
    const result = await query(
      'SELECT id, name, email, phone, device_id, status FROM couriers WHERE id = $1',
      [decoded.id]
    )

    if (result.rows.length === 0) {
      return res.status(401).json({ message: 'Invalid token' })
    }

    const courier = result.rows[0]

    // Check if courier is active
    if (courier.status !== 'active') {
      return res.status(403).json({ message: 'Account is not active' })
    }

    // Attach courier to request
    req.courier = courier

    next()
  } catch (error) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({ message: 'Invalid token' })
    }
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({ message: 'Token expired' })
    }
    return res.status(500).json({ message: 'Authentication failed' })
  }
}

/**
 * Optional authentication - doesn't fail if no token
 */
export const optionalAuth = async (req, res, next) => {
  try {
    const authHeader = req.headers.authorization

    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.split(' ')[1]
      const decoded = jwt.verify(token, process.env.JWT_SECRET)

      const result = await query(
        'SELECT id, name, email, phone, device_id, status FROM couriers WHERE id = $1',
        [decoded.id]
      )

      if (result.rows.length > 0) {
        req.courier = result.rows[0]
      }
    }

    next()
  } catch (error) {
    // Ignore auth errors for optional auth
    next()
  }
}

export default { authenticate, optionalAuth }
