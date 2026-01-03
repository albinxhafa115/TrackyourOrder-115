-- Kuosht GPS Tracking System
-- Database Schema Migration
-- Version: 1.0
-- Date: 2026-01-02

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "postgis"; -- Optional: for advanced geospatial queries

-- ============================================================================
-- TABLES
-- ============================================================================

-- Couriers table
CREATE TABLE IF NOT EXISTS couriers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    device_id VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active', -- active, inactive, suspended
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_couriers_email ON couriers(email);
CREATE INDEX idx_couriers_device_id ON couriers(device_id);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_email ON customers(email);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(100) UNIQUE NOT NULL,
    customer_id INTEGER REFERENCES customers(id) ON DELETE SET NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255),

    -- Delivery details
    delivery_address TEXT NOT NULL,
    delivery_lat DECIMAL(10, 8) NOT NULL,
    delivery_lng DECIMAL(11, 8) NOT NULL,
    delivery_notes TEXT,

    -- Order details
    order_value DECIMAL(10, 2),
    payment_method VARCHAR(50), -- cash, card, online

    -- Assignment
    courier_id INTEGER REFERENCES couriers(id) ON DELETE SET NULL,
    assigned_at TIMESTAMP,

    -- Status tracking
    status VARCHAR(50) DEFAULT 'pending', -- pending, confirmed, out_for_delivery, delivered, cancelled, rescheduled

    -- Scheduling
    scheduled_date DATE DEFAULT CURRENT_DATE,
    scheduled_time_slot VARCHAR(50), -- morning, afternoon

    -- Completion
    completed_at TIMESTAMP,
    completion_type VARCHAR(50), -- delivered_to_customer, delivered_to_family, left_at_door

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_orders_order_number ON orders(order_number);
CREATE INDEX idx_orders_courier_id ON orders(courier_id);
CREATE INDEX idx_orders_customer_id ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_scheduled_date ON orders(scheduled_date);
CREATE INDEX idx_orders_location ON orders(delivery_lat, delivery_lng);

-- Tracking data table (GPS coordinates) -- 24h retention
CREATE TABLE IF NOT EXISTS tracking_data (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    courier_id INTEGER REFERENCES couriers(id) ON DELETE CASCADE,
    device_id VARCHAR(255),

    -- Location data
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(8, 2), -- in meters
    altitude DECIMAL(8, 2),

    -- Movement data
    speed DECIMAL(6, 2), -- km/h
    heading DECIMAL(5, 2), -- degrees

    -- Device data
    battery INTEGER, -- percentage

    -- Metadata
    timestamp BIGINT NOT NULL, -- Unix timestamp from GPS
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tracking_order_id ON tracking_data(order_id);
CREATE INDEX idx_tracking_courier_id ON tracking_data(courier_id);
CREATE INDEX idx_tracking_timestamp ON tracking_data(timestamp);
CREATE INDEX idx_tracking_created_at ON tracking_data(created_at);
CREATE INDEX idx_tracking_location ON tracking_data(lat, lng);

-- Delivery events table
CREATE TABLE IF NOT EXISTS delivery_events (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    courier_id INTEGER REFERENCES couriers(id) ON DELETE SET NULL,

    event_type VARCHAR(100) NOT NULL, -- order_created, assigned, started, arrived, delivered, cancelled, rescheduled
    description TEXT,
    event_data JSONB, -- Additional structured data

    -- Location at event time
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_events_order_id ON delivery_events(order_id);
CREATE INDEX idx_events_event_type ON delivery_events(event_type);
CREATE INDEX idx_events_created_at ON delivery_events(created_at);

-- Reschedules table
CREATE TABLE IF NOT EXISTS reschedules (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    courier_id INTEGER REFERENCES couriers(id) ON DELETE SET NULL,

    original_date DATE NOT NULL,
    new_date DATE NOT NULL,

    reason VARCHAR(255),
    preferred_time VARCHAR(50), -- morning, afternoon
    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_reschedules_order_id ON reschedules(order_id);
CREATE INDEX idx_reschedules_new_date ON reschedules(new_date);

-- ============================================================================
-- FUNCTIONS
-- ============================================================================

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Triggers for updated_at
CREATE TRIGGER update_couriers_updated_at BEFORE UPDATE ON couriers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_customers_updated_at BEFORE UPDATE ON customers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- VIEWS
-- ============================================================================

-- View for active deliveries
CREATE OR REPLACE VIEW active_deliveries AS
SELECT
    o.id,
    o.order_number,
    o.customer_name,
    o.customer_phone,
    o.delivery_address,
    o.delivery_lat,
    o.delivery_lng,
    o.status,
    o.courier_id,
    c.name as courier_name,
    c.device_id,
    o.scheduled_date,
    o.assigned_at
FROM orders o
LEFT JOIN couriers c ON o.courier_id = c.id
WHERE o.status IN ('confirmed', 'out_for_delivery')
ORDER BY o.scheduled_date, o.assigned_at;

-- View for today's orders per courier
CREATE OR REPLACE VIEW todays_orders AS
SELECT
    o.id,
    o.order_number,
    o.customer_name,
    o.customer_phone,
    o.delivery_address,
    o.delivery_lat,
    o.delivery_lng,
    o.status,
    o.courier_id,
    c.name as courier_name,
    o.scheduled_date
FROM orders o
LEFT JOIN couriers c ON o.courier_id = c.id
WHERE o.scheduled_date = CURRENT_DATE
AND o.status NOT IN ('delivered', 'cancelled')
ORDER BY o.id;

-- ============================================================================
-- SAMPLE DATA (for testing)
-- ============================================================================

-- Insert sample courier (password: 'courier123')
INSERT INTO couriers (name, email, password_hash, phone, device_id) VALUES
('Leart Krasniqi', 'leart@kuosht.com', '$2a$10$rQzN3vqY1Z9kX5lH6qJ7.OXzWqR8vQZKLX5jJ8KhZvJ5Lj6KjZJQe', '+38344123456', 'courier_1')
ON CONFLICT (email) DO NOTHING;

-- Insert sample customer
INSERT INTO customers (name, email, phone) VALUES
('Agron Mustafa', 'agron@example.com', '+38344111222'),
('Besarta Krasniqi', 'besarta@example.com', '+38344222333')
ON CONFLICT DO NOTHING;

-- Insert sample orders
INSERT INTO orders (
    order_number, customer_id, customer_name, customer_phone, customer_email,
    delivery_address, delivery_lat, delivery_lng,
    status, scheduled_date, payment_method
) VALUES
(
    'KU20260102001',
    1,
    'Agron Mustafa',
    '+38344111222',
    'agron@example.com',
    'Rruga Fehmi Agani 12, Prishtinë',
    42.6629,
    21.1655,
    'confirmed',
    CURRENT_DATE,
    'cash'
),
(
    'KU20260102002',
    2,
    'Besarta Krasniqi',
    '+38344222333',
    'besarta@example.com',
    'Lagjia Muhaxherëve 8, Prishtinë',
    42.6550,
    21.1600,
    'confirmed',
    CURRENT_DATE,
    'card'
)
ON CONFLICT (order_number) DO NOTHING;

-- ============================================================================
-- CLEANUP FUNCTION (Delete old tracking data)
-- ============================================================================

CREATE OR REPLACE FUNCTION cleanup_old_tracking_data()
RETURNS void AS $$
BEGIN
    DELETE FROM tracking_data
    WHERE created_at < NOW() - INTERVAL '24 hours';

    RAISE NOTICE 'Cleaned up old tracking data';
END;
$$ LANGUAGE plpgsql;

-- Schedule cleanup (Note: This requires pg_cron extension or external cron job)
-- For now, this can be called manually or via a cron job from Node.js

-- ============================================================================
-- GRANT PERMISSIONS (adjust as needed)
-- ============================================================================

-- Grant permissions to application user (create user first if needed)
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO kuosht_app_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO kuosht_app_user;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

DO $$
BEGIN
    RAISE NOTICE '============================================';
    RAISE NOTICE 'Database schema initialized successfully!';
    RAISE NOTICE 'Tables created: couriers, customers, orders, tracking_data, delivery_events, reschedules';
    RAISE NOTICE 'Sample data inserted for testing';
    RAISE NOTICE '============================================';
END $$;
