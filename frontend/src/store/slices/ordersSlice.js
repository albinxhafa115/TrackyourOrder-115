import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import api from '../../services/api'

// Fetch today's orders for courier
export const fetchTodayOrders = createAsyncThunk(
  'orders/fetchToday',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/courier/orders/today')
      return response.data.orders
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch orders')
    }
  }
)

// Start delivery
export const startDelivery = createAsyncThunk(
  'orders/startDelivery',
  async ({ courierId, orderIds }, { rejectWithValue }) => {
    try {
      const response = await api.post('/delivery/start', {
        courier_id: courierId,
        order_ids: orderIds,
      })
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to start delivery')
    }
  }
)

const initialState = {
  orders: [],
  selectedOrders: [],
  activeOrders: [],
  currentOrder: null,
  isLoading: false,
  error: null,
}

const ordersSlice = createSlice({
  name: 'orders',
  initialState,
  reducers: {
    toggleOrderSelection: (state, action) => {
      const orderId = action.payload
      const index = state.selectedOrders.indexOf(orderId)
      if (index > -1) {
        state.selectedOrders.splice(index, 1)
      } else {
        state.selectedOrders.push(orderId)
      }
    },
    selectAllOrders: (state) => {
      state.selectedOrders = state.orders.map((order) => order.id)
    },
    clearSelection: (state) => {
      state.selectedOrders = []
    },
    setCurrentOrder: (state, action) => {
      state.currentOrder = action.payload
    },
    clearError: (state) => {
      state.error = null
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch today's orders
      .addCase(fetchTodayOrders.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(fetchTodayOrders.fulfilled, (state, action) => {
        state.isLoading = false
        state.orders = action.payload
        state.error = null
      })
      .addCase(fetchTodayOrders.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
      // Start delivery
      .addCase(startDelivery.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(startDelivery.fulfilled, (state, action) => {
        state.isLoading = false
        state.activeOrders = state.orders.filter((order) =>
          state.selectedOrders.includes(order.id)
        )
        state.currentOrder = state.activeOrders[0] || null
        state.error = null
      })
      .addCase(startDelivery.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
  },
})

export const {
  toggleOrderSelection,
  selectAllOrders,
  clearSelection,
  setCurrentOrder,
  clearError,
} = ordersSlice.actions

export default ordersSlice.reducer
