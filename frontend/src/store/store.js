import { configureStore } from '@reduxjs/toolkit'
import authReducer from './slices/authSlice'
import ordersReducer from './slices/ordersSlice'
import trackingReducer from './slices/trackingSlice'
import deliveryReducer from './slices/deliverySlice'

export const store = configureStore({
  reducer: {
    auth: authReducer,
    orders: ordersReducer,
    tracking: trackingReducer,
    delivery: deliveryReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        // Ignore these action types
        ignoredActions: ['tracking/updatePosition'],
        // Ignore these field paths in all actions
        ignoredActionPaths: ['payload.timestamp'],
        // Ignore these paths in the state
        ignoredPaths: ['tracking.position.timestamp'],
      },
    }),
})
