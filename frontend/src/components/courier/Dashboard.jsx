import { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { useNavigate } from 'react-router-dom'
import {
  fetchTodayOrders,
  startDelivery,
  toggleOrderSelection,
  selectAllOrders,
  clearSelection,
} from '../../store/slices/ordersSlice'
import { logout } from '../../store/slices/authSlice'
import { formatDistance } from '../../utils/distance'
import { format } from 'date-fns'

const Dashboard = () => {
  const dispatch = useDispatch()
  const navigate = useNavigate()
  const { courier } = useSelector((state) => state.auth)
  const { orders: rawOrders, selectedOrders = [], isLoading } = useSelector(
    (state) => state.orders
  )
  const [showMapView, setShowMapView] = useState(false)
  const orders = Array.isArray(rawOrders) ? rawOrders : []

  useEffect(() => {
    // Fetch today's orders on mount
    dispatch(fetchTodayOrders())
  }, [dispatch])

  const handleStartDelivery = async () => {
    if (!courier?.id) {
      alert('Te lutem kycu perseri.')
      return
    }
    if (selectedOrders.length === 0) {
      alert('Zgjedh të paktën një porosi')
      return
    }

    const result = await dispatch(
      startDelivery({
        courierId: courier.id,
        orderIds: selectedOrders,
      })
    )

    if (result.type === 'orders/startDelivery/fulfilled') {
      navigate('/courier/active')
    }
  }

  const handleLogout = () => {
    if (confirm('A je i sigurt që dëshiron të dalësh?')) {
      dispatch(logout())
      navigate('/courier/login')
    }
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-10">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-xl font-bold text-gray-900">
                Përshëndetje, {courier?.name}
              </h1>
              <p className="text-sm text-gray-600">
                {format(new Date(), 'dd MMMM yyyy')}
              </p>
            </div>
            <div className="flex items-center space-x-3">
              <button
                onClick={() => dispatch(fetchTodayOrders())}
                className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                title="Rifresko"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
              </button>
              <button
                onClick={handleLogout}
                className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                title="Dil"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 py-6">
        {/* Stats */}
        <div className="grid grid-cols-3 gap-4 mb-6">
          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Totali</p>
            <p className="text-2xl font-bold text-gray-900">{orders.length}</p>
          </div>
          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Të zgjedhura</p>
            <p className="text-2xl font-bold text-primary-600">{selectedOrders.length}</p>
          </div>
          <div className="bg-white p-4 rounded-lg shadow">
            <p className="text-sm text-gray-600">Statusi</p>
            <p className="text-sm font-medium text-green-600">Aktiv</p>
          </div>
        </div>

        {/* Title */}
        <div className="mb-4">
          <h2 className="text-lg font-bold text-gray-900">
            Porosi për Sot - {format(new Date(), 'dd MMMM yyyy')}
          </h2>
        </div>

        {/* Loading State */}
        {isLoading && (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>
        )}

        {/* Orders List */}
        {!isLoading && orders.length > 0 && (
          <>
            <div className="space-y-3 mb-6">
              {orders.map((order) => (
                <div
                  key={order.id}
                  className={`bg-white rounded-lg shadow-sm p-4 border-2 transition ${
                    selectedOrders.includes(order.id)
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-transparent'
                  }`}
                  onClick={() => dispatch(toggleOrderSelection(order.id))}
                >
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center mb-2">
                        <input
                          type="checkbox"
                          checked={selectedOrders.includes(order.id)}
                          onChange={() => {}}
                          className="h-5 w-5 text-primary-600 rounded mr-3"
                        />
                        <h3 className="font-bold text-gray-900">
                          #{order.order_number} - {order.customer_name}
                        </h3>
                      </div>
                      <p className="text-sm text-gray-600 ml-8 mb-2">
                        {order.delivery_address}
                      </p>
                      <div className="flex items-center ml-8 space-x-4">
                        <span className="text-sm text-primary-600 font-medium">
                          {formatDistance(order.distance_km)} | ~{Math.ceil(order.distance_km * 2)} min
                        </span>
                        {order.customer_phone && (
                          <span className="text-sm text-gray-500">
                            {order.customer_phone}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Actions */}
            <div className="sticky bottom-0 bg-white border-t-2 border-gray-200 p-4 shadow-lg">
              <div className="flex items-center justify-between mb-3">
                <button
                  onClick={() => {
                    if (selectedOrders.length === orders.length) {
                      dispatch(clearSelection())
                    } else {
                      dispatch(selectAllOrders())
                    }
                  }}
                  className="text-sm text-primary-600 font-medium"
                >
                  {selectedOrders.length === orders.length
                    ? 'Hiqi të gjitha'
                    : 'Zgjedh të gjitha'}
                </button>
                <button
                  onClick={() => setShowMapView(!showMapView)}
                  className="text-sm text-gray-600 flex items-center"
                >
                  <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                    />
                  </svg>
                  Pamja Hartë
                </button>
              </div>

              <button
                onClick={handleStartDelivery}
                disabled={selectedOrders.length === 0}
                className="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-4 px-6 rounded-lg transition duration-200 text-lg"
              >
                FILLO DORËZIMIN ({selectedOrders.length})
              </button>
            </div>
          </>
        )}

        {/* Empty State */}
        {!isLoading && orders.length === 0 && (
          <div className="text-center py-12">
            <svg
              className="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
              />
            </svg>
            <h3 className="mt-2 text-sm font-medium text-gray-900">Asnjë porosi</h3>
            <p className="mt-1 text-sm text-gray-500">
              Nuk ka porosi të planifikuara për sot.
            </p>
          </div>
        )}
      </main>
    </div>
  )
}

export default Dashboard
