/**
 * Calculate distance between two coordinates using Haversine formula
 * @param {Object} coord1 - {lat, lng}
 * @param {Object} coord2 - {lat, lng}
 * @returns {number} - Distance in kilometers
 */
export const calculateDistance = (coord1, coord2) => {
  if (!coord1 || !coord2 || !coord1.lat || !coord1.lng || !coord2.lat || !coord2.lng) {
    return null
  }

  const R = 6371 // Earth's radius in kilometers
  const dLat = toRadians(coord2.lat - coord1.lat)
  const dLon = toRadians(coord2.lng - coord1.lng)

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRadians(coord1.lat)) *
      Math.cos(toRadians(coord2.lat)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2)

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  const distance = R * c

  return distance
}

/**
 * Convert degrees to radians
 */
const toRadians = (degrees) => {
  return (degrees * Math.PI) / 180
}

/**
 * Format distance for display
 */
export const formatDistance = (distanceKm) => {
  if (distanceKm === null || distanceKm === undefined) return 'N/A'

  if (distanceKm < 1) {
    return `${Math.round(distanceKm * 1000)}m`
  }

  return `${distanceKm.toFixed(1)}km`
}

/**
 * Calculate bearing between two points
 */
export const calculateBearing = (coord1, coord2) => {
  if (!coord1 || !coord2) return 0

  const dLon = toRadians(coord2.lng - coord1.lng)
  const lat1 = toRadians(coord1.lat)
  const lat2 = toRadians(coord2.lat)

  const y = Math.sin(dLon) * Math.cos(lat2)
  const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon)

  const bearing = Math.atan2(y, x)
  const bearingDegrees = (bearing * 180) / Math.PI

  return (bearingDegrees + 360) % 360
}
