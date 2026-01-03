import { calculateDistance } from './distance'

/**
 * Calculate estimated time of arrival
 * @param {Object} courierPos - Current courier position {lat, lng}
 * @param {Object} destinationPos - Destination {lat, lng}
 * @param {number} avgSpeed - Average speed in km/h (default: 30 km/h in city)
 * @returns {Object} - {minutes, timeString, distance}
 */
export const calculateETA = (courierPos, destinationPos, avgSpeed = 30) => {
  const distance = calculateDistance(courierPos, destinationPos)

  if (distance === null) {
    return {
      minutes: null,
      timeString: 'N/A',
      distance: null,
    }
  }

  // Calculate time in hours, then convert to minutes
  const timeHours = distance / avgSpeed
  const timeMinutes = Math.ceil(timeHours * 60)

  // Add 5-minute buffer
  const bufferedMinutes = timeMinutes + 5

  return {
    minutes: bufferedMinutes,
    timeString: formatTime(bufferedMinutes),
    distance: distance.toFixed(2),
  }
}

/**
 * Format minutes to human-readable string
 */
const formatTime = (minutes) => {
  if (minutes < 1) return 'Më pak se 1 min'
  if (minutes < 60) return `~${minutes} min`

  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60

  if (mins === 0) return `~${hours}h`
  return `~${hours}h ${mins}min`
}

/**
 * Get ETA timestamp
 */
export const getETATimestamp = (minutes) => {
  if (!minutes) return null

  const now = new Date()
  const eta = new Date(now.getTime() + minutes * 60000)

  return eta.toLocaleTimeString('sq-AL', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Calculate delivery progress percentage
 */
export const calculateProgress = (startPos, currentPos, endPos) => {
  if (!startPos || !currentPos || !endPos) return 0

  const totalDistance = calculateDistance(startPos, endPos)
  const remainingDistance = calculateDistance(currentPos, endPos)

  if (totalDistance === null || remainingDistance === null) return 0

  const progress = ((totalDistance - remainingDistance) / totalDistance) * 100

  return Math.min(Math.max(progress, 0), 100)
}
