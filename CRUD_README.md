# CRUD System Documentation - Valora

---

## Database Setup

### Files
- **database.sql** - Contains all database schema and sample data

### Tables Created
1. **users** - Stores user information with roles (admin/customer)
2. **products** - Stores product information with categories
3. **cart** - Stores shopping cart items
4. **orders** - Stores order information
5. **order_items** - Stores individual items in orders

### Sample Admin User
- **Username:** admin
- **Email:** admin@valora.com
- **Password:** admin123

## Project Structure

```
Valora/
├── config.php                    # Database configuration
├── database.sql                  # Database schema
├── dao/                          # Data Access Objects
│   ├── user_dao.php             # User CRUD operations
│   └── product_dao.php          # Product CRUD operations
├── admin/                        # Admin Dashboard
│   ├── dashboard.php            # Admin main dashboard
│   ├── users/
│   │   ├── admin_user_list.php      # List all users
│   │   ├── admin_user_create.php    # Create new user
│   │   ├── admin_user_edit.php      # Edit user
│   │   └── admin_user_delete.php    # Delete user
│   ├── products/
│   │   ├── admin_product_list.php   # List all products
│   │   ├── admin_product_create.php # Create new product
│   │   ├── admin_product_edit.php   # Edit product
│   │   └── admin_product_delete.php # Delete product
└── process/
    ├── admin_check.php          # Admin authentication helper
    ├── login-process.php        # Login handler
    ├── logout.php               # Logout handler
    └── signup-process.php       # Signup handler
```

---

## DAO Classes

### UserDAO
File: `dao/user_dao.php`

**Methods:**
- `getAllUsers()` - Retrieve all users
- `getUserById($id)` - Get user by ID
- `getUserByUsername($username)` - Get user by username
- `getUserByEmail($email)` - Get user by email
- `createUser($username, $email, $password, $first_name, $last_name, $role)` - Create new user
- `updateUser($id, $email, $first_name, $last_name, $phone)` - Update user details
- `updateUserRole($id, $role)` - Change user role
- `deleteUser($id)` - Delete user
- `emailExists($email)` - Check if email exists
- `usernameExists($username)` - Check if username exists
- `getUserCount()` - Get total user count

### ProductDAO
File: `dao/product_dao.php`

**Methods:**
- `getAllProducts($active_only)` - Retrieve all products
- `getProductById($id)` - Get product by ID
- `getProductsByCategory($category, $active_only)` - Get products by category
- `createProduct($name, $description, $price, $category, $image_path, $stock_quantity)` - Create new product
- `updateProduct($id, $name, $description, $price, $category, $image_path, $stock_quantity)` - Update product
- `updateStock($id, $quantity)` - Update product stock
- `toggleProductStatus($id)` - Toggle product active status
- `deleteProduct($id)` - Delete product
- `getProductCount($active_only)` - Get total product count
- `searchProducts($keyword, $active_only)` - Search products
- `getLowStockProducts($threshold)` - Get products with low stock

---

## Admin Features

### Dashboard
- **File:** `admin/dashboard.php`
- **Features:**
  - Overview statistics (users, products, low stock items)
  - Low stock alerts
  - Quick navigation to management sections

### User Management
- **List Users:** `admin/users/admin_user_list.php`
  - View all users with details
  - Search and filter capabilities
  - Edit and delete actions

- **Create User:** `admin/users/admin_user_create.php`
  - Add new customer or admin user
  - Form validation
  - Duplicate username/email checks

- **Edit User:** `admin/users/admin_user_edit.php`
  - Modify user details
  - Change user role (customer/admin)
  - Email uniqueness validation

- **Delete User:** `admin/users/admin_user_delete.php`
  - Remove user account
  - Prevents self-deletion

### Product Management
- **List Products:** `admin/products/admin_product_list.php`
  - View all products
  - Stock status indicators
  - Edit and delete actions

- **Create Product:** `admin/products/admin_product_create.php`
  - Add new products
  - Image upload support
  - Category selection (dress, gown, collection)
  - Stock management

- **Edit Product:** `admin/products/admin_product_edit.php`
  - Update product information
  - Change product image
  - Modify pricing and stock
  - View current image

- **Delete Product:** `admin/products/admin_product_delete.php`
  - Remove products
  - Auto-delete associated images

---

## Security Features

1. **Session Management**
   - Admin role verification
   - User authentication checks
   - Automatic redirect for unauthorized access

2. **Password Security**
   - BCRYPT password hashing
   - Password confirmation validation

3. **Data Validation**
   - Form input validation
   - Email format validation
   - File type validation (images only)
   - Duplicate checks

4. **SQL Injection Prevention**
   - Prepared statements
   - Parameter binding
   - PDO usage

---

## How to Use

### 1. Setup Database
```sql
-- Import database.sql into your MySQL database
mysql -u root -p valora_db < database.sql
```

### 2. Access Admin Panel
- Navigate to: `http://localhost/Valora/admin/dashboard.php`
- Login with:
  - Username: `admin`
  - Password: `admin123`

### 3. User Management
- Go to Admin Dashboard → Users
- Create, edit, or delete users
- Assign roles (customer/admin)

### 4. Product Management
- Go to Admin Dashboard → Products
- Add new products with images
- Update stock and pricing
- Monitor low stock alerts

---

## Image Upload

### Supported Formats
- JPG
- JPEG
- PNG
- GIF

### Storage Location
- `assets/images/dress/` - Dress products
- `assets/images/gown/` - Gown products
- `assets/images/collection/` - Collection products

### File Naming
- Images are renamed with unique ID prefix to prevent conflicts
- Format: `[uniqid]_[original_filename]`

---

## Database Schema

### Users Table
```sql
id (INT) - Primary Key
username (VARCHAR 255) - Unique
email (VARCHAR 255) - Unique
password (VARCHAR 255) - Hashed
first_name (VARCHAR 100)
last_name (VARCHAR 100)
phone (VARCHAR 15)
role (ENUM 'customer', 'admin')
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### Products Table
```sql
id (INT) - Primary Key
name (VARCHAR 255)
description (TEXT)
price (DECIMAL 10,2)
category (ENUM 'collection', 'dress', 'gown')
image_path (VARCHAR 255)
stock_quantity (INT)
is_active (TINYINT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

---

## Error Handling

- All pages include form validation
- Error messages displayed to users
- Database errors caught and logged
- Graceful redirects on failures

---

## Future Enhancements

1. Order management (view, edit, delete orders)
2. Customer analytics and reports
3. Product reviews and ratings
4. Inventory tracking and alerts
5. Email notifications
6. Advanced search and filtering
7. Export/import functionality
8. Admin activity logging

---

## Technical Stack

- **Language:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Session Management:** PHP Sessions
- **Security:** BCRYPT Password Hashing, Prepared Statements
- **File Uploads:** Image validation and storage

---

## Support
