# User Authentication Flow

## Overview
This documentation outlines the authentication flow for the application using **JWT-based authentication**. The system allows users to **register, log in, and verify authentication** through secure JSON Web Tokens (JWT). The authentication flow is now handled **server-side using PHP sessions (`$_SESSION`)**, ensuring seamless login persistence without relying on browser storage.

## Database Schema (Users Table)
The `users` table is structured as follows:

| Column        | Type         | Description                           |
|--------------|-------------|---------------------------------------|
| id           | INT (PK)     | Unique user ID                        |
| nom          | VARCHAR(255) | User's full name (used as `username`) |
| email        | VARCHAR(255) | User email (unique)                   |
| mot_de_passe | VARCHAR(255) | Hashed password                       |

---

## API Endpoints

### **1. User Registration**
#### **Endpoint:**
```http
POST /api/user/register
```
#### **Request Body:**
```json
{
    "nom": "John Doe",
    "email": "johndoe@example.com",
    "mot_de_passe": "mypassword123"
}
```
#### **Response:**
```json
{
    "success": true,
    "user_id": 1
}
```
#### **Flow:**
1. Validate input fields (`nom`, `email`, `mot_de_passe`).
2. Hash the password before storing it.
3. Insert the user into the database.
4. Return the newly created user ID.

---

### **2. User Login (JWT Token Generation & Session Storage)**
#### **Endpoint:**
```http
POST /api/user/login
```
#### **Request Body:**
```json
{
    "email": "johndoe@example.com",
    "mot_de_passe": "mypassword123"
}
```
#### **Response:**
```json
{
    "success": true,
    "user_name": "John Doe"
}
```
#### **Flow:**
1. Validate input fields (`email`, `mot_de_passe`).
2. Fetch user from the database by `email`.
3. Verify the hashed password using `password_verify()`.
4. Generate a JWT token using `JWTService::generateToken()`.
5. **Store the token in `$_SESSION['jwt']`** for server-side authentication.
6. Return `user_name`.

---

### **3. Token Verification (Protected Routes Using Sessions)**
#### **Middleware:**
Before accessing protected endpoints, authentication is verified via **PHP sessions**.

#### **Flow:**
1. Check if `$_SESSION['jwt']` exists.
2. Decode and verify the JWT using `JWTService::verifyJWT()`.
3. If valid, extract user details and allow access.
4. If invalid, return `401 Unauthorized`.

#### **Example Verification Response:**
```json
{
    "user_id": 1,
    "email": "johndoe@example.com",
    "user_name": "John Doe"
}
```

---

### **4. User Logout (Session Destruction)**
#### **Endpoint:**
```http
GET /api/user/logout
```
#### **Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```
#### **Flow:**
1. **Destroy the session** (`session_destroy()`) to remove JWT storage.
2. Return a logout confirmation message.

---

## **JWT Token Structure**
The JWT token consists of three parts:
```
Header.Payload.Signature
```
Example decoded JWT payload:
```json
{
    "iat": 1710012345,
    "exp": 1710015945,
    "user_id": 1,
    "email": "johndoe@example.com",
    "user_name": "John Doe"
}
```

---

## **Security Measures**
✅ **Password Hashing:** Uses `password_hash()` for secure storage.
✅ **JWT Expiration:** Token expires after a defined time (`JWT_EXPIRES_TIME`).
✅ **Session-Based Authentication:** Prevents reliance on browser storage.
✅ **Authorization Middleware:** Prevents access to protected routes without a valid JWT session.
✅ **Environment Variables:** Stores JWT secrets securely using `.env`.
✅ **Secure `.env` Protection:** Prevents direct access via `.htaccess` or Nginx rules.

---

## **Environment Configuration (`.env`)**
The system loads sensitive credentials from `.env`:
```env
JWT_SECRET=your_long_random_secret
JWT_ALGORITHM=HS256
JWT_EXPIRES_TIME=3600
```

**Ensure `.env` is protected using:**
**Apache (`.htaccess`):**
```apache
<FilesMatch "^\.env">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```
**Nginx:**
```nginx
location ~ /\. {
    deny all;
}
```

---

## **Backend Authentication (Using PHP Sessions)**
### **Storing JWT in PHP Sessions (`Auth.php`)**
Upon successful login, the JWT is stored in `$_SESSION['jwt']`.

```php
session_start();
$_SESSION['jwt'] = $jwt;
```

### **Verifying Authentication (`JWTService::verifyJWT()`)**
```php
session_start();
if (!isset($_SESSION['jwt'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
$decoded = JWTService::getUserFromToken($_SESSION['jwt']);
```

### **Logging Out (Destroying Session)**
```php
session_start();
session_unset();
session_destroy();
echo json_encode(["success" => true, "message" => "Logged out successfully"]);
```

---

