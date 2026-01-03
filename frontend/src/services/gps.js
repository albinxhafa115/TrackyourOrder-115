import api from './api'
import { openDB } from './storage'

/**
 * Send GPS update to backend
 * Falls back to IndexedDB if offline
 */
export const sendGPSUpdate = async (data) => {
  try {
    await api.post('/gps/update', data)
    return true
  } catch (error) {
    // If offline, store in IndexedDB for later sync
    if (!navigator.onLine) {
      await storeOfflineGPSUpdate(data)
    }
    console.error('Failed to send GPS update:', error)
    return false
  }
}

/**
 * Store GPS update in IndexedDB when offline
 */
const storeOfflineGPSUpdate = async (data) => {
  try {
    const db = await openDB()
    const tx = db.transaction('gps_updates', 'readwrite')
    const store = tx.objectStore('gps_updates')
    await store.add({
      ...data,
      stored_at: new Date().toISOString(),
    })
  } catch (error) {
    console.error('Failed to store offline GPS update:', error)
  }
}

/**
 * Sync offline GPS updates when back online
 */
export const syncOfflineGPSUpdates = async () => {
  try {
    const db = await openDB()
    const tx = db.transaction('gps_updates', 'readonly')
    const store = tx.objectStore('gps_updates')
    const updates = await store.getAll()

    if (updates.length === 0) return

    // Send all updates to backend
    for (const update of updates) {
      try {
        await api.post('/gps/update', update)
        // Delete from IndexedDB after successful sync
        const deleteTx = db.transaction('gps_updates', 'readwrite')
        const deleteStore = deleteTx.objectStore('gps_updates')
        await deleteStore.delete(update.id)
      } catch (error) {
        console.error('Failed to sync GPS update:', error)
      }
    }

    console.log(`Synced ${updates.length} offline GPS updates`)
  } catch (error) {
    console.error('Failed to sync offline GPS updates:', error)
  }
}

/**
 * Calculate proximity to destination
 * Returns distance in kilometers
 */
export const calculateProximity = (courierPos, destinationPos) => {
  if (!courierPos || !destinationPos) return null

  const R = 6371 // Earth's radius in km
  const dLat = toRad(destinationPos.lat - courierPos.lat)
  const dLon = toRad(destinationPos.lng - courierPos.lng)

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(courierPos.lat)) *
      Math.cos(toRad(destinationPos.lat)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2)

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  const distance = R * c

  return distance
}

const toRad = (deg) => {
  return (deg * Math.PI) / 180
}

/**
 * Trigger auto-call when within proximity
 */
export const checkProximityAndCall = (courierPos, order, threshold = 4) => {
  const distance = calculateProximity(courierPos, {
    lat: order.delivery_lat,
    lng: order.delivery_lng,
  })

  if (distance !== null && distance <= threshold) {
    return {
      shouldCall: true,
      distance: distance.toFixed(2),
      phone: order.customer_phone,
    }
  }

  return { shouldCall: false, distance: distance?.toFixed(2) || null }
}
