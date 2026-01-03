import { useState } from 'react'
import { useSelector, useDispatch } from 'react-redux'
import { closeCompletionModal, completeDelivery, cancelDelivery, rescheduleDelivery } from '../../store/slices/deliverySlice'
import { setCurrentOrder } from '../../store/slices/ordersSlice'

const DeliveryCompletion = () => {
  const dispatch = useDispatch()
  const { completionModal, isLoading, error } = useSelector((state) => state.delivery)
  const { activeOrders } = useSelector((state) => state.orders)

  const [selectedOption, setSelectedOption] = useState(null)
  const [notes, setNotes] = useState('')
  const [rescheduleDate, setRescheduleDate] = useState('')
  const [preferredTime, setPreferredTime] = useState('morning')

  const { isOpen, orderId, type } = completionModal

  const handleClose = () => {
    dispatch(closeCompletionModal())
    setSelectedOption(null)
    setNotes('')
    setRescheduleDate('')
  }

  const handleSubmit = async () => {
    if (!selectedOption && type !== 'delivered') {
      alert('Zgjedh një opsion')
      return
    }

    const data = {
      notes,
    }

    let result

    if (type === 'delivered') {
      result = await dispatch(completeDelivery({
        orderId,
        data: {
          ...data,
          status: 'delivered',
          completion_type: selectedOption || 'delivered_to_customer',
        },
      }))
    } else if (type === 'cancelled') {
      result = await dispatch(cancelDelivery({
        orderId,
        data: {
          ...data,
          reason: selectedOption,
        },
      }))
    } else if (type === 'reschedule') {
      if (!rescheduleDate) {
        alert('Zgjedh datën e re')
        return
      }
      result = await dispatch(rescheduleDelivery({
        orderId,
        data: {
          ...data,
          new_date: rescheduleDate,
          reason: selectedOption,
          preferred_time: preferredTime,
        },
      }))
    }

    if (result.type.endsWith('/fulfilled')) {
      // Move to next order
      const currentIndex = activeOrders.findIndex((o) => o.id === orderId)
      const nextOrder = activeOrders[currentIndex + 1]

      if (nextOrder) {
        dispatch(setCurrentOrder(nextOrder))
      }

      handleClose()
    }
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="sticky top-0 bg-white border-b px-6 py-4">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-gray-900">
              {type === 'delivered' && 'Dorëzo Porosinë'}
              {type === 'cancelled' && 'Anulo Porosinë'}
              {type === 'reschedule' && 'Shtyrë Porosinë'}
            </h2>
            <button
              onClick={handleClose}
              className="p-1 hover:bg-gray-100 rounded-lg"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        {/* Error */}
        {error && (
          <div className="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p className="text-sm text-red-700">{error}</p>
          </div>
        )}

        {/* Content */}
        <div className="p-6 space-y-6">
          {/* DELIVERED Options */}
          {type === 'delivered' && (
            <div className="space-y-3">
              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="delivery_type"
                  value="delivered_to_customer"
                  checked={selectedOption === 'delivered_to_customer'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-primary-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">I dorëzuar klientit</p>
                  <p className="text-sm text-gray-600">Klienti e mori personalisht</p>
                </div>
              </label>

              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="delivery_type"
                  value="delivered_to_family"
                  checked={selectedOption === 'delivered_to_family'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-primary-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">I dorëzuar familjarit</p>
                  <p className="text-sm text-gray-600">E mori një anëtar i familjes</p>
                </div>
              </label>

              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="delivery_type"
                  value="left_at_door"
                  checked={selectedOption === 'left_at_door'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-primary-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">Lënë në derë</p>
                  <p className="text-sm text-gray-600">Lënë sipas kërkesës së klientit</p>
                </div>
              </label>
            </div>
          )}

          {/* CANCELLED Options */}
          {type === 'cancelled' && (
            <div className="space-y-3">
              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="cancel_reason"
                  value="customer_refused"
                  checked={selectedOption === 'customer_refused'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-red-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">Klienti refuzon</p>
                  <p className="text-sm text-gray-600">Nuk e dëshiron më produktin</p>
                </div>
              </label>

              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="cancel_reason"
                  value="wrong_address"
                  checked={selectedOption === 'wrong_address'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-red-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">Adresë e gabuar</p>
                  <p className="text-sm text-gray-600">Adresa nuk ekziston</p>
                </div>
              </label>

              <label className="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <input
                  type="radio"
                  name="cancel_reason"
                  value="no_answer"
                  checked={selectedOption === 'no_answer'}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="mt-1 h-4 w-4 text-red-600"
                />
                <div className="ml-3">
                  <p className="font-medium text-gray-900">Klienti nuk përgjigjet</p>
                  <p className="text-sm text-gray-600">Nuk e merr telefonin/nuk hap derën</p>
                </div>
              </label>
            </div>
          )}

          {/* RESCHEDULE Options */}
          {type === 'reschedule' && (
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Arsyeja:
                </label>
                <select
                  value={selectedOption || ''}
                  onChange={(e) => setSelectedOption(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Zgjedh arsyjen</option>
                  <option value="customer_not_home">Klienti nuk është në shtëpi</option>
                  <option value="customer_requested">Klienti kërkon datë tjetër</option>
                  <option value="inconvenient_time">Kohë e papërshtatshme</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Data e re:
                </label>
                <input
                  type="date"
                  value={rescheduleDate}
                  onChange={(e) => setRescheduleDate(e.target.value)}
                  min={new Date(Date.now() + 86400000).toISOString().split('T')[0]}
                  max={new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Koha preferuar:
                </label>
                <div className="space-y-2">
                  <label className="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input
                      type="radio"
                      name="preferred_time"
                      value="morning"
                      checked={preferredTime === 'morning'}
                      onChange={(e) => setPreferredTime(e.target.value)}
                      className="h-4 w-4 text-primary-600"
                    />
                    <span className="ml-3">Mëngjes (09:00-12:00)</span>
                  </label>
                  <label className="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input
                      type="radio"
                      name="preferred_time"
                      value="afternoon"
                      checked={preferredTime === 'afternoon'}
                      onChange={(e) => setPreferredTime(e.target.value)}
                      className="h-4 w-4 text-primary-600"
                    />
                    <span className="ml-3">Pasdite (12:00-18:00)</span>
                  </label>
                </div>
              </div>
            </div>
          )}

          {/* Notes */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Shënime (opsionale):
            </label>
            <textarea
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              rows={3}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              placeholder="Shto shënime shtesë..."
            />
          </div>
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-gray-50 px-6 py-4 border-t flex space-x-3">
          <button
            onClick={handleClose}
            disabled={isLoading}
            className="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-3 px-4 rounded-lg disabled:opacity-50"
          >
            Anulo
          </button>
          <button
            onClick={handleSubmit}
            disabled={isLoading}
            className="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg disabled:opacity-50 flex items-center justify-center"
          >
            {isLoading ? (
              <svg
                className="animate-spin h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                ></circle>
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            ) : (
              'KONFIRMO'
            )}
          </button>
        </div>
      </div>
    </div>
  )
}

export default DeliveryCompletion
