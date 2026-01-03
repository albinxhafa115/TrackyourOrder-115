import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { MapContainer, TileLayer, Marker, Popup, Polyline } from 'react-leaflet'
import L from 'leaflet'
import api from '../../services/api'
import { formatDistance } from '../../utils/distance'
import { format } from 'date-fns'

// Fix Leaflet default icon issue
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const TrackingPage = () => {
  const { orderNumber } = useParams()
  const [trackingData, setTrackingData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [autoRefresh, setAutoRefresh] = useState(true)

  const fetchTrackingData = async () => {
    try {
      const response = await api.get(`/track/${orderNumber}`)
      setTrackingData(response.data)
      setError(null)
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to fetch tracking data')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchTrackingData()

    // Auto-refresh every 30 seconds
    let interval
    if (autoRefresh) {
      interval = setInterval(fetchTrackingData, 30000)
    }

    return () => {
      if (interval) clearInterval(interval)
    }
  }, [orderNumber, autoRefresh])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-primary-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
          <svg
            className="mx-auto h-12 w-12 text-red-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h2 className="mt-4 text-xl font-bold text-gray-900">Gabim</h2>
          <p className="mt-2 text-gray-600">{error}</p>
          <button
            onClick={fetchTrackingData}
            className="mt-6 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-lg"
          >
            Riprovo
          </button>
        </div>
      </div>
    )
  }

  const { order, current_position, eta, events } = trackingData

  const getStatusColor = (status) => {
    const colors = {
      pending: 'bg-yellow-100 text-yellow-800',
      confirmed: 'bg-blue-100 text-blue-800',
      out_for_delivery: 'bg-purple-100 text-purple-800',
      delivered: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800',
      rescheduled: 'bg-orange-100 text-orange-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }

  const getStatusText = (status) => {
    const texts = {
      pending: 'Në Pritje',
      confirmed: 'E Konfirmuar',
      out_for_delivery: 'Në Transport',
      delivered: 'E Dorëzuar',
      cancelled: 'E Anuluar',
      rescheduled: 'E Shtyrë',
    }
    return texts[status] || status
  }

  const liveStatuses = new Set([
    'out_for_delivery',
    'in_transit',
    'in_transport',
    'in_delivery',
    'started',
  ])
  const isLiveTracking = !!current_position && liveStatuses.has(order.status)

  const progressPercentage =
    order.status === 'delivered'
      ? 100
      : isLiveTracking
      ? 65
      : order.status === 'confirmed'
      ? 33
      : 10

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm">
        <div className="max-w-4xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold text-primary-600">KUOSHT</h1>
            <span className={`px-4 py-2 rounded-full text-sm font-medium ${getStatusColor(order.status)}`}>
              {getStatusText(order.status)}
            </span>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-4xl mx-auto px-4 py-6">
        {/* Order Info */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 className="text-xl font-bold text-gray-900 mb-2">
            Porosia juaj: #{order.order_number}
          </h2>
          <p className="text-gray-600 mb-4">{order.delivery_address}</p>

          {/* Progress Bar */}
          <div className="mb-4">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium text-gray-700">Progresi</span>
              <span className="text-sm text-gray-600">{progressPercentage}%</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <div
                className="bg-primary-600 h-2 rounded-full transition-all duration-500"
                style={{ width: `${progressPercentage}%` }}
              ></div>
            </div>
          </div>

          {/* ETA & Distance */}
          {isLiveTracking && (
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-primary-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600">Arrin për:</p>
                <p className="text-2xl font-bold text-primary-600">{eta.time_string || 'Calculating...'}</p>
              </div>
              <div className="bg-gray-50 p-4 rounded-lg">
                <p className="text-sm text-gray-600">Distanca:</p>
                <p className="text-2xl font-bold text-gray-900">
                  {formatDistance(eta.distance_km)}
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Live Map */}
        {isLiveTracking && (
          <div className="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div className="p-4 border-b flex items-center justify-between">
              <h3 className="font-bold text-gray-900">Harta Live</h3>
              <div className="flex items-center">
                <span className="flex items-center text-sm text-green-600 mr-4">
                  <span className="animate-pulse inline-block w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                  Live
                </span>
                <button
                  onClick={() => setAutoRefresh(!autoRefresh)}
                  className={`text-sm ${autoRefresh ? 'text-primary-600' : 'text-gray-400'}`}
                >
                  Auto-refresh: {autoRefresh ? 'ON' : 'OFF'}
                </button>
              </div>
            </div>
            <div className="h-96">
              <MapContainer
                center={[current_position.lat, current_position.lng]}
                zoom={14}
                style={{ height: '100%', width: '100%' }}
              >
                <TileLayer
                  attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                  url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />

                {/* Courier Position */}
                <Marker position={[current_position.lat, current_position.lng]}>
                  <Popup>
                    Kurieri<br />
                    Update: {format(new Date(current_position.timestamp), 'HH:mm:ss')}
                  </Popup>
                </Marker>

                {/* Destination */}
                <Marker position={[order.delivery_lat, order.delivery_lng]}>
                  <Popup>
                    Destinacioni<br />
                    {order.delivery_address}
                  </Popup>
                </Marker>

                {/* Route Line */}
                <Polyline
                  positions={[
                    [current_position.lat, current_position.lng],
                    [order.delivery_lat, order.delivery_lng],
                  ]}
                  color="blue"
                  weight={3}
                  opacity={0.7}
                  dashArray="10, 10"
                />
              </MapContainer>
            </div>
            <div className="p-3 bg-gray-50 text-sm text-gray-600 text-center">
              Update i fundit: {format(new Date(current_position.timestamp), 'HH:mm:ss')}
            </div>
          </div>
        )}

        {/* Status Timeline */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="font-bold text-gray-900 mb-4">Historiku i Statusit</h3>
          <div className="space-y-4">
            {events && events.length > 0 ? (
              events.map((event, index) => (
                <div key={index} className="flex items-start">
                  <div className="flex-shrink-0">
                    <div
                      className={`h-10 w-10 rounded-full flex items-center justify-center ${
                        index === 0 ? 'bg-primary-600' : 'bg-gray-300'
                      }`}
                    >
                      <svg
                        className="h-5 w-5 text-white"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fillRule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clipRule="evenodd"
                        />
                      </svg>
                    </div>
                  </div>
                  <div className="ml-4 flex-1">
                    <p className="text-sm font-medium text-gray-900">
                      {getStatusText(event.event_type)}
                    </p>
                    <p className="text-sm text-gray-600">{event.description}</p>
                    <p className="text-xs text-gray-500 mt-1">
                      {format(new Date(event.created_at), 'dd MMM yyyy, HH:mm')}
                    </p>
                  </div>
                </div>
              ))
            ) : (
              <p className="text-gray-500 text-center py-4">Asnjë event i regjistruar</p>
            )}
          </div>
        </div>

        {/* Info Notice */}
        <div className="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
          <div className="flex items-start">
            <svg className="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path
                fillRule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clipRule="evenodd"
              />
            </svg>
            <div className="text-sm text-blue-700">
              <p className="font-medium">Informacion i Rëndësishëm</p>
              <p>
                Kurieri do t'ju telefonojë kur të jetë afër (3-4 km). Kjo faqe rifresohet automatikisht
                çdo 30 sekonda.
              </p>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-white border-t mt-12">
        <div className="max-w-4xl mx-auto px-4 py-6 text-center text-sm text-gray-600">
          <p>&copy; 2025 Kuosht. Powered by GPS Tracking PWA.</p>
        </div>
      </footer>
    </div>
  )
}

export default TrackingPage
