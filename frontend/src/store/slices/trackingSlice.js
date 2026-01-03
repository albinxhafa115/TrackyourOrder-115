import { createSlice } from '@reduxjs/toolkit'

const initialState = {
  position: null,
  isTracking: false,
  error: null,
  watchId: null,
  lastUpdate: null,
}

const trackingSlice = createSlice({
  name: 'tracking',
  initialState,
  reducers: {
    startTracking: (state) => {
      state.isTracking = true
      state.error = null
    },
    stopTracking: (state) => {
      state.isTracking = false
      state.watchId = null
    },
    updatePosition: (state, action) => {
      state.position = action.payload
      state.lastUpdate = new Date().toISOString()
      state.error = null
    },
    setWatchId: (state, action) => {
      state.watchId = action.payload
    },
    setTrackingError: (state, action) => {
      state.error = action.payload
      state.isTracking = false
    },
    clearTrackingError: (state) => {
      state.error = null
    },
  },
})

export const {
  startTracking,
  stopTracking,
  updatePosition,
  setWatchId,
  setTrackingError,
  clearTrackingError,
} = trackingSlice.actions

export default trackingSlice.reducer
