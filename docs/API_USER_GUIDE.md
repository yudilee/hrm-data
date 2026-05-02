# Personal API Access — User Guide

This guide explains how to use the Personal Access Tokens (PATs) generated within the Master Data Hub to access dealership data programmatically.

## 1. Generating a Token

1. Log in to the [Master Data Hub](https://hrm-data.hartonomotor-group.com).
2. Navigate to **Developer → My API Tokens** in the sidebar.
3. Enter a descriptive name (e.g., "Excel-Sync-Script").
4. Select the permissions (Abilities) you need.
5. Click **Generate Token**.
6. **IMPORTANT:** Copy the token immediately. For security reasons, it will never be shown again.

## 2. Authentication

The API uses **Bearer Token** authentication. You must include your token in the `Authorization` header of every HTTP request.

**Header Format:**
```http
Authorization: Bearer <your_token_here>
```

## 3. API Version & Base URL

All current requests should use **API v2**.

**Base URL:** `https://hrm-data.hartonomotor-group.com/api/v2`

## 4. Available Endpoints

| Endpoint | Required Ability | Description |
|---|---|---|
| `GET /customers` | `read:customers` | List and search master customer records |
| `GET /vehicles` | `read:vehicles` | List and search master vehicle records |
| `GET /service-histories` | `read:service-histories` | View detailed vehicle service history |
| `GET /suppliers` | `read:suppliers` | List master supplier records |
| `GET /labour-codes` | `read:labour-codes` | Access the standard labour code catalogue |
| `GET /search?q=...` | `search` | Global cross-entity search |

## 5. Usage Examples

### Python (Requests)
```python
import requests

url = "https://hrm-data.hartonomotor-group.com/api/v2/vehicles"
headers = {
    "Authorization": "Bearer YOUR_TOKEN_HERE",
    "Accept": "application/json"
}

response = requests.get(url, headers=headers)
data = response.json()
print(data)
```

### cURL
```bash
curl -X GET "https://hrm-data.hartonomotor-group.com/api/v2/vehicles" \
     -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     -H "Accept: application/json"
```

## 6. Limits and Policies

### Rate Limiting
Regular user tokens are limited to **60 requests per minute**. If you exceed this limit, the API will return an `HTTP 429 Too Many Requests` error.

### Security & Auditing
- **IP Allowlist:** If your administrator has configured an IP allowlist, your token will only work from those specific IP addresses.
- **Logging:** Every request made with your token is logged, including your User ID, IP address, and the specific endpoint accessed.
- **Principle of Least Privilege:** Only grant your tokens the minimum abilities needed for the task.

## 7. Interactive Documentation
For a full list of fields, schemas, and interactive testing, visit the [API Documentation Portal](https://hrm-data.hartonomotor-group.com/docs/api).
