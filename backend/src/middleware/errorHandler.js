/**
 * Global error handler middleware
 */
export const errorHandler = (err, req, res, next) => {
  console.error('Error:', err)

  // Default error
  let status = err.status || 500
  let message = err.message || 'Internal server error'

  // PostgreSQL errors
  if (err.code) {
    switch (err.code) {
      case '23505': // Unique violation
        status = 409
        message = 'Resource already exists'
        break
      case '23503': // Foreign key violation
        status = 400
        message = 'Invalid reference'
        break
      case '22P02': // Invalid text representation
        status = 400
        message = 'Invalid input'
        break
    }
  }

  // Send error response
  res.status(status).json({
    error: {
      message,
      status,
      ...(process.env.NODE_ENV === 'development' && { stack: err.stack }),
    },
  })
}

export default { errorHandler }
