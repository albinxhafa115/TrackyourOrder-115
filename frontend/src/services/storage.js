/**
 * IndexedDB wrapper for offline storage
 * Stores GPS updates, orders, and delivery actions when offline
 */

const DB_NAME = 'kuosht_offline_db'
const DB_VERSION = 1

export const openDB = () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION)

    request.onerror = () => {
      reject(new Error('Failed to open IndexedDB'))
    }

    request.onsuccess = (event) => {
      resolve(event.target.result)
    }

    request.onupgradeneeded = (event) => {
      const db = event.target.result

      // GPS updates store
      if (!db.objectStoreNames.contains('gps_updates')) {
        const gpsStore = db.createObjectStore('gps_updates', {
          keyPath: 'id',
          autoIncrement: true,
        })
        gpsStore.createIndex('timestamp', 'timestamp', { unique: false })
        gpsStore.createIndex('stored_at', 'stored_at', { unique: false })
      }

      // Cached orders store
      if (!db.objectStoreNames.contains('orders')) {
        const ordersStore = db.createObjectStore('orders', {
          keyPath: 'id',
        })
        ordersStore.createIndex('status', 'status', { unique: false })
        ordersStore.createIndex('cached_at', 'cached_at', { unique: false })
      }

      // Pending delivery actions store
      if (!db.objectStoreNames.contains('pending_actions')) {
        const actionsStore = db.createObjectStore('pending_actions', {
          keyPath: 'id',
          autoIncrement: true,
        })
        actionsStore.createIndex('action_type', 'action_type', { unique: false })
        actionsStore.createIndex('created_at', 'created_at', { unique: false })
      }
    }
  })
}

/**
 * Cache orders for offline access
 */
export const cacheOrders = async (orders) => {
  try {
    const db = await openDB()
    const tx = db.transaction('orders', 'readwrite')
    const store = tx.objectStore('orders')

    for (const order of orders) {
      await store.put({
        ...order,
        cached_at: new Date().toISOString(),
      })
    }

    console.log(`Cached ${orders.length} orders`)
  } catch (error) {
    console.error('Failed to cache orders:', error)
  }
}

/**
 * Get cached orders
 */
export const getCachedOrders = async () => {
  try {
    const db = await openDB()
    const tx = db.transaction('orders', 'readonly')
    const store = tx.objectStore('orders')
    const orders = await store.getAll()
    return orders
  } catch (error) {
    console.error('Failed to get cached orders:', error)
    return []
  }
}

/**
 * Store pending delivery action (complete, cancel, reschedule)
 */
export const storePendingAction = async (actionType, orderId, data) => {
  try {
    const db = await openDB()
    const tx = db.transaction('pending_actions', 'readwrite')
    const store = tx.objectStore('pending_actions')

    await store.add({
      action_type: actionType,
      order_id: orderId,
      data,
      created_at: new Date().toISOString(),
    })

    console.log(`Stored pending ${actionType} action for order ${orderId}`)
  } catch (error) {
    console.error('Failed to store pending action:', error)
  }
}

/**
 * Sync pending actions when back online
 */
export const syncPendingActions = async () => {
  try {
    const db = await openDB()
    const tx = db.transaction('pending_actions', 'readonly')
    const store = tx.objectStore('pending_actions')
    const actions = await store.getAll()

    if (actions.length === 0) return

    const api = (await import('./api')).default

    for (const action of actions) {
      try {
        let endpoint = ''
        switch (action.action_type) {
          case 'complete':
            endpoint = `/delivery/complete/${action.order_id}`
            break
          case 'cancel':
            endpoint = `/delivery/cancel/${action.order_id}`
            break
          case 'reschedule':
            endpoint = `/delivery/reschedule/${action.order_id}`
            break
          default:
            continue
        }

        await api.post(endpoint, action.data)

        // Delete from IndexedDB after successful sync
        const deleteTx = db.transaction('pending_actions', 'readwrite')
        const deleteStore = deleteTx.objectStore('pending_actions')
        await deleteStore.delete(action.id)
      } catch (error) {
        console.error(`Failed to sync ${action.action_type} action:`, error)
      }
    }

    console.log(`Synced ${actions.length} pending actions`)
  } catch (error) {
    console.error('Failed to sync pending actions:', error)
  }
}

/**
 * Clear all offline data
 */
export const clearOfflineData = async () => {
  try {
    const db = await openDB()

    // Clear all stores
    const stores = ['gps_updates', 'orders', 'pending_actions']
    for (const storeName of stores) {
      const tx = db.transaction(storeName, 'readwrite')
      const store = tx.objectStore(storeName)
      await store.clear()
    }

    console.log('Cleared all offline data')
  } catch (error) {
    console.error('Failed to clear offline data:', error)
  }
}
