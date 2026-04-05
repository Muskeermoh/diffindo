diffindo
-- Drop existing tables if any (for development reset)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  role ENUM('admin', 'customer', 'support_staff') DEFAULT 'customer',
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(32) DEFAULT NULL,
  nic VARCHAR(50) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  password_reset_token VARCHAR(255) DEFAULT NULL,
  password_reset_expires DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  delivery_datetime DATETIME NOT NULL,
  total DECIMAL(10, 2) NOT NULL,
  status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  quantity INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sample Admin User
INSERT INTO users (name, email, role, password, phone) VALUES
('Admin', 'admin@diffindo.com', 'admin', SHA2('admin123', 256), NULL);

-- Sample Products
INSERT INTO products (name, description, price, image) VALUES
('Red Velvet Romance', 'Smooth and creamy layers with a hint of chocolate.', 2000, 'red_velvet.jpg'),
('Black Forest Fantasy', 'Classic delight with cherries and chocolate.', 1800, 'black_forest.jpg'),
('Strawberry Shortcake', 'Fresh strawberries and whipped cream.', 1500, 'strawberry.jpg'),
('Chocolate Truffle Bliss', 'Rich chocolate cake with ganache topping.', 2200, 'chocolate_truffle.jpg'),
('Mango Cream Cloud', 'Tropical mango cake with cream filling.', 1900, 'mango_cream.jpg'),
('Rainbow Sprinkle Delight', 'Colorful layers with vanilla cream.', 2100, 'rainbow.jpg'),
('Classic Vanilla Dream', 'Soft vanilla sponge with buttercream.', 1600, 'vanilla.jpg'),
('Salted Caramel Crunch', 'Sweet and salty combo with crunch.', 2300, 'salted_caramel.jpg'),
('Blueberry Cheesecake', 'Creamy cheesecake with blueberry topping.', 2400, 'blueberry.jpg'),
('Nutella Overload', 'Nutella-based cake with hazelnut sprinkles.', 2500, 'nutella.jpg');

-- Feedbacks Table
DROP TABLE IF EXISTS feedbacks;
CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  rating TINYINT UNSIGNED DEFAULT NULL,
  comments TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
