# 📱 How to Test on Your Phone

## ✅ Everything is Running

- ✅ Backend: http://localhost:5000
- ✅ Frontend: http://localhost:4173 (preview mode)
- ✅ Database: Connected
- ✅ Ngrok: https://deana-compony-inger.ngrok-free.dev

---

## 🎯 Test Now

### Option 1: Same WiFi (No GPS)
**Your computer IP:** `192.168.1.4`

**On your phone:**
```
http://192.168.1.4:4173
```

Login: `leart@kuosht.com` / `courier123`

⚠️ GPS won't work (needs HTTPS)

---

### Option 2: With ngrok (GPS Works!)  ⭐

Since ngrok free plan allows only 1 tunnel, we need to serve frontend through the same backend.

**Use same WiFi for now:**
```
http://192.168.1.4:4173
```

**For GPS testing:** You'll need ngrok paid plan OR deploy to production (Vercel/Netlify).

---

## 🔐 Test Credentials

- Email: `leart@kuosht.com`
- Password: `courier123`

## 📍 Customer Tracking

```
http://192.168.1.4:4173/track/KU20260102001
```

---

## ✅ What Works

- ✅ Login
- ✅ Dashboard with orders
- ✅ UI is responsive
- ✅ Customer tracking page
- ❌ GPS (needs HTTPS - use ngrok paid or production)
