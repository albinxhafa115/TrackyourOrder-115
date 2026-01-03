import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import api from '../../services/api'

// Async thunk for login
export const login = createAsyncThunk(
  'auth/login',
  async ({ email, password }, { rejectWithValue }) => {
    try {
      const response = await api.post('/auth/login', { email, password })
      // Store token in localStorage
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('courier', JSON.stringify(response.data.courier))
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Login failed')
    }
  }
)

// Load initial state from localStorage
const storedToken = localStorage.getItem('token')
const storedCourier = localStorage.getItem('courier')

const initialState = {
  token: storedToken || null,
  courier: storedCourier ? JSON.parse(storedCourier) : null,
  isLoading: false,
  error: null,
}

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logout: (state) => {
      state.token = null
      state.courier = null
      state.error = null
      localStorage.removeItem('token')
      localStorage.removeItem('courier')
    },
    clearError: (state) => {
      state.error = null
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(login.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(login.fulfilled, (state, action) => {
        state.isLoading = false
        state.token = action.payload.token
        state.courier = action.payload.courier
        state.error = null
      })
      .addCase(login.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
  },
})

export const { logout, clearError } = authSlice.actions
export default authSlice.reducer
