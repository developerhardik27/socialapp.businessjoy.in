# Login API Usage Examples

## Base URL
```
http://your-domain.com/api
```

## 1. User Login
**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "userpassword"
}
```

**Success Response (200):**
```json
{
    "status": 200,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": 2,
            "company_id": 1,
            "country_id": 1,
            "state_id": 1,
            "city_id": 1,
            "default_module": "invoice",
            "default_page": "invoice"
        },
        "company": {
            "id": 1,
            "name": "Company Name",
            "app_version": "v4_4_4",
            "dbname": "company_db"
        },
        "company_details": {
            "gst_no": "GST123456",
            "country_id": 1,
            "state_id": 1,
            "city_id": 1
        },
        "api_token": "60_character_random_string_here",
        "permissions": {...},
        "token_type": "Bearer",
        "expires_in": 525600
    }
}
```

**Error Responses:**
- **422 (Validation Error):**
```json
{
    "status": 422,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

- **404 (User Not Found):**
```json
{
    "status": 404,
    "message": "Email not registered"
}
```

- **401 (Invalid Credentials):**
```json
{
    "status": 401,
    "message": "Invalid credentials"
}
```

- **403 (Account Issues):**
```json
{
    "status": 403,
    "message": "Account is inactive. Please contact support."
}
```

## 2. User Logout
**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {api_token}
```

**Success Response (200):**
```json
{
    "status": 200,
    "message": "Logout successful"
}
```

## 3. Get User Profile
**Endpoint:** `GET /api/auth/profile`

**Headers:**
```
Authorization: Bearer {api_token}
```

**Success Response (200):**
```json
{
    "status": 200,
    "message": "Profile retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": 2,
            "company_id": 1,
            "country_id": 1,
            "state_id": 1,
            "city_id": 1,
            "default_module": "invoice",
            "default_page": "invoice",
            "is_active": 1,
            "created_at": "2023-01-01T00:00:00.000000Z"
        },
        "company": {
            "id": 1,
            "name": "Company Name",
            "app_version": "v4_4_4"
        },
        "company_details": {
            "gst_no": "GST123456",
            "country_id": 1,
            "state_id": 1,
            "city_id": 1
        }
    }
}
```

## 4. Refresh Token
**Endpoint:** `POST /api/auth/refresh-token`

**Headers:**
```
Authorization: Bearer {api_token}
```

**Success Response (200):**
```json
{
    "status": 200,
    "message": "Token refreshed successfully",
    "data": {
        "api_token": "new_60_character_random_string",
        "token_type": "Bearer",
        "expires_in": 525600
    }
}
```

## Usage Examples

### Using cURL
```bash
# Login
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get Profile (replace TOKEN with actual token)
curl -X GET http://your-domain.com/api/auth/profile \
  -H "Authorization: Bearer TOKEN"

# Logout
curl -X POST http://your-domain.com/api/auth/logout \
  -H "Authorization: Bearer TOKEN"
```

### Using JavaScript/Fetch
```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  
  const data = await response.json();
  if (data.status === 200) {
    // Store token
    localStorage.setItem('api_token', data.data.api_token);
    return data;
  }
  throw new Error(data.message);
};

// Get Profile
const getProfile = async () => {
  const token = localStorage.getItem('api_token');
  const response = await fetch('/api/auth/profile', {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return await response.json();
};
```

### Using Postman
1. Set method to POST
2. URL: `http://your-domain.com/api/auth/login`
3. Headers: `Content-Type: application/json`
4. Body: Select `raw` and `JSON`, then enter:
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

For protected endpoints, add Authorization header:
- Key: `Authorization`
- Value: `Bearer {your_api_token}`

## Security Notes
- Always use HTTPS in production
- Store API tokens securely on client side
- Implement token refresh mechanism for long-running sessions
- The API token acts as the authentication key - keep it secret
- Tokens are invalidated on logout and when user login status changes
