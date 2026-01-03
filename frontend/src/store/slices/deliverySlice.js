import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import api from '../../services/api'

// Complete delivery
export const completeDelivery = createAsyncThunk(
  'delivery/complete',
  async ({ orderId, data }, { rejectWithValue }) => {
    try {
      const response = await api.post(`/delivery/complete/${orderId}`, data)
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to complete delivery')
    }
  }
)

// Cancel delivery
export const cancelDelivery = createAsyncThunk(
  'delivery/cancel',
  async ({ orderId, data }, { rejectWithValue }) => {
    try {
      const response = await api.post(`/delivery/cancel/${orderId}`, data)
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to cancel delivery')
    }
  }
)

// Reschedule delivery
export const rescheduleDelivery = createAsyncThunk(
  'delivery/reschedule',
  async ({ orderId, data }, { rejectWithValue }) => {
    try {
      const response = await api.post(`/delivery/reschedule/${orderId}`, data)
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to reschedule delivery')
    }
  }
)

const initialState = {
  completionModal: {
    isOpen: false,
    orderId: null,
    type: null, // 'delivered', 'cancelled', 'reschedule'
  },
  isLoading: false,
  error: null,
  lastAction: null,
}

const deliverySlice = createSlice({
  name: 'delivery',
  initialState,
  reducers: {
    openCompletionModal: (state, action) => {
      state.completionModal = {
        isOpen: true,
        orderId: action.payload.orderId,
        type: action.payload.type,
      }
    },
    closeCompletionModal: (state) => {
      state.completionModal = {
        isOpen: false,
        orderId: null,
        type: null,
      }
      state.error = null
    },
    clearError: (state) => {
      state.error = null
    },
  },
  extraReducers: (builder) => {
    builder
      // Complete delivery
      .addCase(completeDelivery.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(completeDelivery.fulfilled, (state, action) => {
        state.isLoading = false
        state.lastAction = 'completed'
        state.completionModal.isOpen = false
      })
      .addCase(completeDelivery.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
      // Cancel delivery
      .addCase(cancelDelivery.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(cancelDelivery.fulfilled, (state, action) => {
        state.isLoading = false
        state.lastAction = 'cancelled'
        state.completionModal.isOpen = false
      })
      .addCase(cancelDelivery.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
      // Reschedule delivery
      .addCase(rescheduleDelivery.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(rescheduleDelivery.fulfilled, (state, action) => {
        state.isLoading = false
        state.lastAction = 'rescheduled'
        state.completionModal.isOpen = false
      })
      .addCase(rescheduleDelivery.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
  },
})

export const { openCompletionModal, closeCompletionModal, clearError } = deliverySlice.actions
export default deliverySlice.reducer
