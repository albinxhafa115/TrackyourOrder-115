import { useEffect, useState } from 'react'
import { useSelector, useDispatch } from 'react-redux'
import { useNavigate } from 'react-router-dom'
import { MapContainer, Marker, Popup, TileLayer, Polyline } from 'react-leaflet'
import L from 'leaflet'
import useGeolocation from '../../hooks/useGeolocation'
import { openCompletionModal } from '../../store/slices/deliverySlice'
import { setCurrentOrder } from '../../store/slices/ordersSlice'
import { calculateDistance, formatDistance } from '../../utils/distance'
import { calculateETA, getETATimestamp } from '../../utils/eta'
import { makeCall, shouldAutoCall } from '../../utils/navigation'
import DeliveryCompletion from './DeliveryCompletion'

// Fix Leaflet default icon issue
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const ActiveDelivery = () => {
  const dispatch = useDispatch()
  const navigate = useNavigate()

  const { activeOrders, currentOrder } = useSelector((state) => state.orders)
  const { courier } = useSelector((state) => state.auth)
  const { position, isTracking, error, startTracking, stopTracking } = useGeolocation()

  const [showCallPrompt, setShowCallPrompt] = useState(false)
  const [batteryLevel, setBatteryLevel] = useState(null)
  const [showMap, setShowMap] = useState(false)

  // Redirect if no active orders
  useEffect(() => {
    if (!activeOrders || activeOrders.length === 0) {
      navigate('/courier/dashboard')
    }
  }, [activeOrders, navigate])

  // Start GPS tracking on mount
  useEffect(() => {
    if (!isTracking) {
      startTracking()
    }

    // Get battery level
    if (navigator.getBattery) {
      navigator.getBattery().then((battery) => {
        setBatteryLevel(Math.round(battery.level * 100))

        battery.addEventListener('levelchange', () => {
          setBatteryLevel(Math.round(battery.level * 100))
        })
      })
    }

    return () => {
      // Stop tracking when component unmounts
      stopTracking()
    }
  }, [])

  // Check proximity for auto-call
  useEffect(() => {
    if (!position || !currentOrder) return

    const distance = calculateDistance(position, {
      lat: currentOrder.delivery_lat,
      lng: currentOrder.delivery_lng,
    })

    if (shouldAutoCall(distance) && !showCallPrompt) {
      setShowCallPrompt(true)
    }
  }, [position, currentOrder, showCallPrompt])

  if (!currentOrder) return null

  const currentDistance = position
    ? calculateDistance(position, {
        lat: currentOrder.delivery_lat,
        lng: currentOrder.delivery_lng,
      })
    : null

  const eta = position
    ? calculateETA(position, {
        lat: currentOrder.delivery_lat,
        lng: currentOrder.delivery_lng,
      })
    : { minutes: null, timeString: 'Calculating...' }

  const completedCount = activeOrders.filter((o) => o.status === 'delivered').length
  const mapCenter = position
    ? [position.lat, position.lng]
    : [Number(currentOrder.delivery_lat), Number(currentOrder.delivery_lng)]

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-10">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <button
                onClick={() => navigate('/courier/dashboard')}
                className="p-2 hover:bg-gray-100 rounded-lg mr-2"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M15 19l-7-7 7-7"
                  />
                </svg>
              </button>
              <h1 className="text-lg font-bold text-gray-900">Dorëzime Aktive</h1>
            </div>
            <div className="flex items-center space-x-2">
              {isTracking && (
                <span className="flex items-center text-sm text-green-600">
                  <span className="animate-pulse inline-block w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                  GPS Active
                </span>
              )}
              {batteryLevel !== null && (
                <span className="text-sm text-gray-600">
                  Battery: {batteryLevel}%
                </span>
              )}
            </div>
          </div>
        </div>
      </header>

      {/* GPS Error */}
      {error && (
        <div className="bg-red-50 border-l-4 border-red-500 p-4 m-4">
          <div className="flex items-start">
            <svg className="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path
                fillRule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clipRule="evenodd"
              />
            </svg>
            <div>
              <p className="text-sm font-medium text-red-800">GPS Error</p>
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        </div>
      )}

      {/* Auto-Call Prompt */}
      {showCallPrompt && currentOrder.customer_phone && (
        <div className="bg-blue-50 border-l-4 border-blue-500 p-4 m-4">
          <div className="flex items-start justify-between">
            <div>
              <p className="text-sm font-medium text-blue-800">
                Ti je afër destinacionit ({formatDistance(currentDistance)})
              </p>
              <p className="text-sm text-blue-700">Thirr klientin tani?</p>
            </div>
            <div className="flex space-x-2">
              <button
                onClick={() => {
                  makeCall(currentOrder.customer_phone)
                  setShowCallPrompt(false)
                }}
                className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
              >
                PO
              </button>
              <button
                onClick={() => setShowCallPrompt(false)}
                className="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-400"
              >
                JO
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 py-4">
        {/* Progress */}
        <div className="bg-white rounded-lg shadow-sm p-4 mb-4">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-gray-700">
              Progresi: {completedCount}/{activeOrders.length}
            </span>
            <span className="text-sm text-gray-600">
              {Math.round((completedCount / activeOrders.length) * 100)}%
            </span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="bg-primary-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${(completedCount / activeOrders.length) * 100}%` }}
            ></div>
          </div>
        </div>

        {/* Current Order */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-bold text-gray-900">TANI:</h2>
            <span className="px-3 py-1 bg-primary-100 text-primary-800 text-sm font-medium rounded-full">
              Në Transport
            </span>
          </div>

          <div className="space-y-3">
            <div>
              <p className="text-xl font-bold text-gray-900">
                #{currentOrder.order_number} - {currentOrder.customer_name}
              </p>
              <p className="text-gray-600">{currentOrder.delivery_address}</p>
            </div>

            <div className="flex items-center space-x-4">
              {currentOrder.customer_phone && (
                <a
                  href={`tel:${currentOrder.customer_phone}`}
                  className="flex items-center text-primary-600 hover:text-primary-800"
                >
                  <svg className="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                    />
                  </svg>
                  {currentOrder.customer_phone}
                </a>
              )}
            </div>

            <div className="grid grid-cols-2 gap-4 mt-4">
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="text-sm text-gray-600">Distanca</p>
                <p className="text-lg font-bold text-gray-900">
                  {formatDistance(currentDistance)}
                </p>
              </div>
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="text-sm text-gray-600">ETA</p>
                <p className="text-lg font-bold text-gray-900">{eta.timeString}</p>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="grid grid-cols-2 gap-3 mt-6">
              <button
                onClick={() => setShowMap((prev) => !prev)}
                className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center"
              >
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                  />
                </svg>
                HARTA
              </button>
              <button
                onClick={() =>
                  dispatch(
                    openCompletionModal({
                      orderId: currentOrder.id,
                      type: 'delivered',
                    })
                  )
                }
                className="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center"
              >
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                DORËZUAR
              </button>
            </div>
          </div>
        </div>

        {/* Live Map */}
        {showMap && (
          <div className="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div className="p-4 border-b flex items-center justify-between">
              <h3 className="font-bold text-gray-900">Harta Live</h3>
              <span className="text-sm text-gray-600">
                {position ? 'GPS aktive' : 'Duke pritur GPS'}
              </span>
            </div>
            <div className="h-80">
              <MapContainer center={mapCenter} zoom={14} style={{ height: '100%', width: '100%' }}>
                <TileLayer
                  attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                  url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />
                {position && (
                  <Marker position={[position.lat, position.lng]}>
                    <Popup>Kurieri</Popup>
                  </Marker>
                )}
                <Marker position={[currentOrder.delivery_lat, currentOrder.delivery_lng]}>
                  <Popup>{currentOrder.delivery_address}</Popup>
                </Marker>
                {position && (
                  <Polyline
                    positions={[
                      [position.lat, position.lng],
                      [currentOrder.delivery_lat, currentOrder.delivery_lng],
                    ]}
                    color="blue"
                    weight={3}
                    opacity={0.7}
                    dashArray="10, 10"
                  />
                )}
              </MapContainer>
            </div>
          </div>
        )}

        {/* Queue */}
        {activeOrders.length > 1 && (
          <div className="bg-white rounded-lg shadow-sm p-4">
            <h3 className="text-sm font-bold text-gray-700 mb-3">RADHAZI:</h3>
            <div className="space-y-2">
              {activeOrders.slice(1).map((order, index) => (
                <div
                  key={order.id}
                  className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div>
                    <p className="font-medium text-gray-900">
                      #{order.order_number} - {order.customer_name}
                    </p>
                    <p className="text-sm text-gray-600">{formatDistance(order.distance_km)}</p>
                  </div>
                  <span className="text-sm text-gray-500">#{index + 2}</span>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Control Buttons */}
        <div className="mt-6 space-y-3">
          <button
            onClick={() => {
              if (confirm('A je i sigurt që dëshiron të bësh pushim?')) {
                navigate('/courier/dashboard')
              }
            }}
            className="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg"
          >
            PUSHIM
          </button>
        </div>
      </main>

      {/* Delivery Completion Modal */}
      <DeliveryCompletion />
    </div>
  )
}

export default ActiveDelivery
