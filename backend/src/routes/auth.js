import express from 'express'
import bcrypt from 'bcryptjs'
import jwt from 'jsonwebtoken'
import { query } from '../config/database.js'

const router = express.Router()

/**
 * POST /api/auth/login
 * Login courier with email and password
 */
router.post('/login', async (req, res, next) => {
  try {
    const { email, password } = req.body

    // Validate input
    if (!email || !password) {
      return res.status(400).json({ message: 'Email and password are required' })
    }

    // Find courier by email
    const result = await query('SELECT * FROM couriers WHERE email = $1', [email])

    if (result.rows.length === 0) {
      return res.status(401).json({ message: 'Invalid credentials' })
    }

    const courier = result.rows[0]

    // Check if courier is active
    if (courier.status !== 'active') {
      return res.status(403).json({ message: 'Account is not active' })
    }

    // Verify password
    const isValid = await bcrypt.compare(password, courier.password_hash)

    if (!isValid) {
      return res.status(401).json({ message: 'Invalid credentials' })
    }

    // Generate JWT token
    const token = jwt.sign(
      { id: courier.id, email: courier.email },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRE || '8h' }
    )

    // Return token and courier data
    res.json({
      token,
      courier: {
        id: courier.id,
        name: courier.name,
        email: courier.email,
        phone: courier.phone,
        device_id: courier.device_id,
      },
    })
  } catch (error) {
    next(error)
  }
})

export default router
