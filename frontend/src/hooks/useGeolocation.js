import { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import {
  startTracking,
  stopTracking,
  updatePosition,
  setWatchId,
  setTrackingError,
} from '../store/slices/trackingSlice'
import { sendGPSUpdate } from '../services/gps'

/**
 * Custom hook for GPS tracking using HTML5 Geolocation API
 * Automatically sends updates to backend every 30 seconds
 */
const useGeolocation = (options = {}) => {
  const dispatch = useDispatch()
  const { position, isTracking, error, watchId } = useSelector((state) => state.tracking)
  const { courier } = useSelector((state) => state.auth)

  const defaultOptions = {
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 0,
    updateInterval: 30000, // 30 seconds
    ...options,
  }

  useEffect(() => {
    if (!isTracking) return

    // Check if geolocation is supported
    if (!navigator.geolocation) {
      dispatch(setTrackingError('Geolocation is not supported by your browser'))
      return
    }

    let lastUpdateTime = Date.now()

    // Success callback
    const onSuccess = (pos) => {
      const positionData = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        accuracy: pos.coords.accuracy,
        speed: pos.coords.speed || 0,
        heading: pos.coords.heading || 0,
        timestamp: pos.timestamp,
      }

      // Update Redux state
      dispatch(updatePosition(positionData))

      // Send to backend every updateInterval (30s)
      const now = Date.now()
      if (now - lastUpdateTime >= defaultOptions.updateInterval) {
        if (courier?.id) {
          sendGPSUpdate({
            device_id: courier.device_id || `courier_${courier.id}`,
            ...positionData,
            battery: navigator.getBattery ? null : 100, // Will be updated if Battery API available
          })
          lastUpdateTime = now
        }
      }
    }

    // Error callback
    const onError = (err) => {
      let errorMessage = 'An unknown error occurred'

      switch (err.code) {
        case err.PERMISSION_DENIED:
          errorMessage = 'Location permission denied. Please enable location access in your browser settings.'
          break
        case err.POSITION_UNAVAILABLE:
          errorMessage = 'Location information is unavailable. Please check your GPS settings.'
          break
        case err.TIMEOUT:
          errorMessage = 'Location request timed out. Please try again.'
          break
        default:
          errorMessage = err.message
      }

      dispatch(setTrackingError(errorMessage))
    }

    // Start watching position
    const id = navigator.geolocation.watchPosition(
      onSuccess,
      onError,
      {
        enableHighAccuracy: defaultOptions.enableHighAccuracy,
        timeout: defaultOptions.timeout,
        maximumAge: defaultOptions.maximumAge,
      }
    )

    dispatch(setWatchId(id))

    // Cleanup function
    return () => {
      if (id) {
        navigator.geolocation.clearWatch(id)
      }
    }
  }, [isTracking, courier, dispatch, defaultOptions.enableHighAccuracy, defaultOptions.timeout, defaultOptions.maximumAge, defaultOptions.updateInterval])

  // Get battery status if available
  useEffect(() => {
    if (!isTracking || !navigator.getBattery) return

    navigator.getBattery().then((battery) => {
      // Update battery level periodically
      const updateBattery = () => {
        if (position) {
          sendGPSUpdate({
            device_id: courier?.device_id || `courier_${courier?.id}`,
            ...position,
            battery: Math.round(battery.level * 100),
          })
        }
      }

      battery.addEventListener('levelchange', updateBattery)
      return () => battery.removeEventListener('levelchange', updateBattery)
    })
  }, [isTracking, position, courier])

  return {
    position,
    isTracking,
    error,
    startTracking: () => dispatch(startTracking()),
    stopTracking: () => dispatch(stopTracking()),
  }
}

export default useGeolocation
