/**
 * Open external navigation app
 * Handles Android, iOS, and fallback
 */
export const openNavigation = (lat, lng, address = '') => {
  const encodedAddress = encodeURIComponent(address)

  // Detect platform
  const isAndroid = /Android/i.test(navigator.userAgent)
  const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent)

  let url

  if (isAndroid) {
    // Android - Try Google Maps first, fallback to geo: intent
    url = `geo:${lat},${lng}?q=${lat},${lng}(${encodedAddress})`

    // Try to open Waze if available
    const wazeUrl = `waze://?ll=${lat},${lng}&navigate=yes`
    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`

    // Show options or default to Google Maps
    if (confirm('Hape në Waze? (Cancel për Google Maps)')) {
      window.location.href = wazeUrl
      // Fallback to Google Maps if Waze not installed
      setTimeout(() => {
        window.location.href = googleMapsUrl
      }, 1000)
    } else {
      window.location.href = googleMapsUrl
    }
    return
  }

  if (isIOS) {
    // iOS - Try Apple Maps first
    url = `maps://?daddr=${lat},${lng}`

    // Fallback to Google Maps web
    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`

    window.location.href = url

    // Fallback if Apple Maps not available
    setTimeout(() => {
      window.location.href = googleMapsUrl
    }, 1000)
    return
  }

  // Desktop/Other - Open Google Maps in new tab
  const desktopUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`
  window.open(desktopUrl, '_blank')
}

/**
 * Make phone call
 * Opens tel: link
 */
export const makeCall = (phoneNumber) => {
  if (!phoneNumber) return

  // Clean phone number (remove spaces, dashes, etc.)
  const cleanPhone = phoneNumber.replace(/[^\d+]/g, '')

  window.location.href = `tel:${cleanPhone}`
}

/**
 * Check if should auto-call based on proximity
 */
export const shouldAutoCall = (distanceKm, threshold = 4) => {
  return distanceKm !== null && distanceKm <= threshold
}

/**
 * Get navigation app preferences
 */
export const getNavigationPreference = () => {
  return localStorage.getItem('nav_preference') || 'google'
}

/**
 * Set navigation app preference
 */
export const setNavigationPreference = (app) => {
  localStorage.setItem('nav_preference', app)
}
