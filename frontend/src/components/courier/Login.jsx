import { useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { useNavigate } from 'react-router-dom'
import { login, clearError } from '../../store/slices/authSlice'

const Login = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [rememberMe, setRememberMe] = useState(false)

  const dispatch = useDispatch()
  const navigate = useNavigate()
  const { isLoading, error, token } = useSelector((state) => state.auth)

  useEffect(() => {
    // If already logged in, redirect to dashboard
    if (token) {
      navigate('/courier/dashboard')
    }
  }, [token, navigate])

  useEffect(() => {
    // Load saved email if remember me was checked
    const savedEmail = localStorage.getItem('remembered_email')
    if (savedEmail) {
      setEmail(savedEmail)
      setRememberMe(true)
    }
  }, [])

  const handleSubmit = async (e) => {
    e.preventDefault()

    // Save email if remember me is checked
    if (rememberMe) {
      localStorage.setItem('remembered_email', email)
    } else {
      localStorage.removeItem('remembered_email')
    }

    // Dispatch login action
    const result = await dispatch(login({ email, password }))

    if (result.type === 'auth/login/fulfilled') {
      navigate('/courier/dashboard')
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-500 to-primary-900 px-4">
      <div className="max-w-md w-full">
        {/* Logo/Brand */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-white mb-2">KUOSHT</h1>
          <p className="text-primary-100">GPS Tracking - Courier Portal</p>
        </div>

        {/* Login Card */}
        <div className="bg-white rounded-lg shadow-xl p-8">
          <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">
            Kyçu si Kurier
          </h2>

          {/* Error Message */}
          {error && (
            <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <div className="flex items-center">
                <svg
                  className="w-5 h-5 text-red-500 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fillRule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clipRule="evenodd"
                  />
                </svg>
                <p className="text-sm text-red-700">{error}</p>
              </div>
              <button
                onClick={() => dispatch(clearError())}
                className="mt-2 text-sm text-red-600 underline"
              >
                Mbylle
              </button>
            </div>
          )}

          {/* Login Form */}
          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="email@example.com"
                disabled={isLoading}
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="••••••••"
                disabled={isLoading}
              />
            </div>

            <div className="flex items-center">
              <input
                id="remember"
                type="checkbox"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                disabled={isLoading}
              />
              <label htmlFor="remember" className="ml-2 block text-sm text-gray-700">
                Më mbaj mend
              </label>
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              {isLoading ? (
                <>
                  <svg
                    className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
                  Duke u kyçur...
                </>
              ) : (
                'KYÇU'
              )}
            </button>

            <div className="text-center">
              <a
                href="#"
                className="text-sm text-primary-600 hover:text-primary-800"
                onClick={(e) => {
                  e.preventDefault()
                  alert('Kontakto administratorin për të rivendosur password-in')
                }}
              >
                E harrove password-in?
              </a>
            </div>
          </form>
        </div>

        {/* Footer */}
        <div className="text-center mt-6">
          <p className="text-sm text-primary-100">
            v1.0.0 - 100% Web-Based PWA
          </p>
        </div>
      </div>
    </div>
  )
}

export default Login
