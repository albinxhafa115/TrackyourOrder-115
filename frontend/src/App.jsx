import { Routes, Route, Navigate } from 'react-router-dom'
import { useSelector } from 'react-redux'

// Courier Routes
import CourierLogin from './components/courier/Login'
import CourierDashboard from './components/courier/Dashboard'
import ActiveDelivery from './components/courier/ActiveDelivery'

// Customer Routes
import TrackingPage from './components/customer/TrackingPage'

// Admin Routes (bonus)
import AdminDashboard from './components/admin/Dashboard'

// Protected Route Component
const ProtectedRoute = ({ children }) => {
  const { token } = useSelector((state) => state.auth)

  if (!token) {
    return <Navigate to="/courier/login" replace />
  }

  return children
}

function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <Routes>
        {/* Courier Routes */}
        <Route path="/courier/login" element={<CourierLogin />} />
        <Route
          path="/courier/dashboard"
          element={
            <ProtectedRoute>
              <CourierDashboard />
            </ProtectedRoute>
          }
        />
        <Route
          path="/courier/active"
          element={
            <ProtectedRoute>
              <ActiveDelivery />
            </ProtectedRoute>
          }
        />

        {/* Customer Tracking Route - Public */}
        <Route path="/track/:orderNumber" element={<TrackingPage />} />

        {/* Admin Routes - Protected */}
        <Route
          path="/admin"
          element={
            <ProtectedRoute>
              <AdminDashboard />
            </ProtectedRoute>
          }
        />

        {/* Default redirect */}
        <Route path="/" element={<Navigate to="/courier/login" replace />} />
        <Route path="/courier" element={<Navigate to="/courier/login" replace />} />

        {/* 404 */}
        <Route path="*" element={<div className="flex items-center justify-center h-screen"><h1 className="text-2xl font-bold">404 - Faqja nuk u gjet</h1></div>} />
      </Routes>
    </div>
  )
}

export default App
