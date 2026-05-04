# RTS Labour Code Integration — Odoo Developer Guide

**Version:** 1.0  
**Date:** May 2026  
**Contact:** IT Department — Hartono Raya Motor Group

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Security Model](#security-model)
4. [Setup & Configuration](#setup--configuration)
5. [Generating Signed URLs](#generating-signed-urls)
6. [Receiving Webhook Callbacks](#receiving-webhook-callbacks)
7. [Odoo Module Implementation](#odoo-module-implementation)
8. [Service History Viewer](#service-history-viewer)
9. [Testing & Debugging](#testing--debugging)
10. [Error Handling](#error-handling)
11. [API Reference](#api-reference)

---

## Overview

This integration allows Odoo workshop Job Orders to pull **RTS labour codes** based on a vehicle's chassis number. The labour code selection UI is hosted on the **RTS Master Data Hub** app — Odoo opens it via a signed URL, the user selects codes, and the results are sent back to Odoo via a webhook callback.

### Key Concepts

- **Labour codes** are grouped by vehicle model prefix (first 6 characters of chassis number)
- One job order can have **multiple labour codes** (one-to-many)
- Each labour code has a `code`, `description`, `group_name`, and `time_hours` (standard hours)
- The RTS app is the **single source of truth** for labour codes

---

## Architecture

```
┌─────────────────────┐         ┌─────────────────────┐
│      ODOO           │         │    RTS APP           │
│  (Your System)      │         │  (Master Data Hub)   │
│                     │         │                      │
│  1. User clicks     │         │                      │
│     "Select Labour" │         │                      │
│                     │  ──2──► │  3. Verify signature  │
│  Generate signed URL│  HTTPS  │  4. Show labour codes │
│  Open in new tab    │         │  5. User selects      │
│                     │         │                      │
│  7. Create labour   │  ◄──6── │  6. POST webhook      │
│     lines on JO     │  HTTPS  │     with signed data  │
│                     │         │                      │
└─────────────────────┘         └─────────────────────┘
```

**Flow:**
1. Service advisor clicks "Select Labour Codes" button on Job Order form
2. Odoo generates an HMAC-signed URL and opens it in a new browser tab
3. RTS app verifies the signature, expiry, and nonce
4. RTS shows the labour code selection page with checkboxes grouped by category
5. User checks the desired codes and clicks "Confirm & Send to Odoo"
6. RTS sends the selected codes to Odoo via a signed HTTP POST (webhook)
7. Odoo receives the webhook, verifies the signature, creates labour lines

---

## Security Model

All communication happens over **HTTPS** on the public internet. There are 4 security layers:

### Layer 1: HMAC-Signed Launch URL (Odoo → RTS)

| Parameter | Description |
|---|---|
| `sig` | HMAC-SHA256 signature of all other parameters |
| `exp` | Unix timestamp — URL expires after this time (default: 5 minutes) |
| `nonce` | Random string — prevents replay attacks (each URL is single-use) |

### Layer 2: Signed Webhook (RTS → Odoo)

| Header | Description |
|---|---|
| `X-RTS-Signature` | HMAC-SHA256 of the POST body |
| `X-RTS-Timestamp` | Unix timestamp of when the request was sent |

### Layer 3: Shared Secrets

Two **separate** secrets are used (defense in depth):

| Secret | Used For | Stored In |
|---|---|---|
| `SHARED_SECRET` | Signing launch URLs (Odoo → RTS) | Odoo: `ir.config_parameter`, RTS: `.env` |
| `WEBHOOK_SECRET` | Signing webhook callbacks (RTS → Odoo) | Odoo: `ir.config_parameter`, RTS: `.env` |

### Layer 4: Transport & Infrastructure

- HTTPS-only (TLS 1.2+)
- Rate limiting (30 requests/minute per IP)
- All requests logged with IP address and user agent

> **⚠️ IMPORTANT:** Both secrets must be generated as random 64-character hex strings and must match exactly between Odoo and RTS. Use `openssl rand -hex 32` to generate them.

---

## Setup & Configuration

### Step 1: Generate Secrets

Run these commands on any Linux/Mac machine:

```bash
# Generate shared secret (for launch URLs)
openssl rand -hex 32
# Example output: a1b2c3d4e5f6...

# Generate webhook secret (for callbacks)  
openssl rand -hex 32
# Example output: f6e5d4c3b2a1...
```

### Step 2: Configure Odoo System Parameters

In Odoo, go to **Settings → Technical → Parameters → System Parameters** and create:

| Key | Value | Example |
|---|---|---|
| `rts.base_url` | RTS app base URL | `https://rts.yourdomain.com` |
| `rts.shared_secret` | Launch URL signing secret | `a1b2c3d4e5f6...` (64 chars) |
| `rts.webhook_secret` | Webhook verification secret | `f6e5d4c3b2a1...` (64 chars) |

### Step 3: Provide RTS Admin with Your Info

Tell the RTS admin:
- Your **webhook callback URL**: `https://odoo.yourdomain.com/rts/labour-callback`
- Your **Odoo server's public IP** (for optional IP allowlist on the RTS API token)
- The **webhook secret** you generated (must match on both sides)
- The **shared secret** you generated (must match on both sides)

---

## Generating Signed URLs

When the user clicks "Select Labour Codes", Odoo must generate a signed URL.

### Python Implementation

```python
import hashlib
import hmac
import os
import time
import urllib.parse


def generate_rts_labour_url(job_order_id, job_number, chassis_no, 
                             customer_name, callback_url):
    """
    Generate an HMAC-signed URL to open the RTS labour selection page.
    
    Args:
        job_order_id: Odoo record ID of the job/repair order
        job_number: Display name of the job order (e.g. "JO-2026-0001")
        chassis_no: Vehicle chassis number (min 6 chars, ideally 17)
        customer_name: Customer display name
        callback_url: Full URL where RTS should POST the selected codes
    
    Returns:
        str: Complete signed URL to open in browser
    """
    # Get config from Odoo system parameters
    ICP = self.env['ir.config_parameter'].sudo()
    base_url = ICP.get_param('rts.base_url', '').rstrip('/')
    shared_secret = ICP.get_param('rts.shared_secret', '')
    
    # Build parameters
    params = {
        'job_order_id': str(job_order_id),
        'job_number': str(job_number),
        'chassis': str(chassis_no).upper(),
        'customer_name': str(customer_name),
        'callback_url': str(callback_url),
        'exp': str(int(time.time()) + 300),  # 5 minute expiry
        'nonce': hashlib.md5(os.urandom(16)).hexdigest(),
    }
    
    # Create HMAC signature from sorted parameters
    sorted_msg = '&'.join(f"{k}={v}" for k, v in sorted(params.items()))
    sig = hmac.new(
        shared_secret.encode('utf-8'),
        sorted_msg.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()
    params['sig'] = sig
    
    return f"{base_url}/odoo/select-labour?{urllib.parse.urlencode(params)}"
```

### URL Parameters Reference

| Parameter | Type | Required | Description |
|---|---|---|---|
| `job_order_id` | string | ✅ | Odoo record ID for the repair/job order |
| `job_number` | string | ✅ | Display name (e.g. "JO-2026-0001") |
| `chassis` | string | ✅ | Vehicle chassis number (min 6 characters) |
| `customer_name` | string | ✅ | Customer name for display |
| `callback_url` | string | ✅ | Odoo webhook URL for receiving results |
| `exp` | string | ✅ | Unix timestamp when URL expires |
| `nonce` | string | ✅ | Random unique string (prevents replay) |
| `sig` | string | ✅ | HMAC-SHA256 signature |

### Signature Algorithm

```
1. Collect all parameters EXCEPT 'sig'
2. Sort parameters alphabetically by key
3. Join as: "key1=value1&key2=value2&..."
4. Compute: HMAC-SHA256(shared_secret, joined_string)
5. Append result as 'sig' parameter
```

### Example

Given parameters (sorted):
```
callback_url=https://odoo.example.com/rts/labour-callback
chassis=MHFAB1BA0KP123456
customer_name=PT HARTONO RAYA
exp=1714567890
job_number=JO-2026-0001
job_order_id=42
nonce=a1b2c3d4e5f67890
```

Message to sign:
```
callback_url=https://odoo.example.com/rts/labour-callback&chassis=MHFAB1BA0KP123456&customer_name=PT HARTONO RAYA&exp=1714567890&job_number=JO-2026-0001&job_order_id=42&nonce=a1b2c3d4e5f67890
```

Signature: `HMAC-SHA256(shared_secret, message)` → append as `&sig=<hex_digest>`

---

## Receiving Webhook Callbacks

After the user selects labour codes, RTS sends a POST request to your `callback_url`.

### Webhook Request Format

```http
POST /rts/labour-callback HTTP/1.1
Host: odoo.yourdomain.com
Content-Type: application/json
Accept: application/json
X-RTS-Signature: 8f14e45fceea167a5a36dedd4bea2543...
X-RTS-Timestamp: 1714567920
```

### Request Body

```json
{
    "job_order_id": "42",
    "source": "rts_labour_app",
    "timestamp": "2026-05-01T17:30:00+07:00",
    "labours": [
        {
            "rts_id": 10,
            "code": "E-001",
            "labour_key": "ENG01",
            "description": "Ganti oli mesin",
            "group_name": "ENGINE",
            "time_hours": 0.5
        },
        {
            "rts_id": 20,
            "code": "B-001",
            "labour_key": "BRK01",
            "description": "Ganti brake pad depan",
            "group_name": "BRAKE SYSTEM",
            "time_hours": 0.75
        }
    ]
}
```

### Labour Object Fields

| Field | Type | Description |
|---|---|---|
| `rts_id` | integer | RTS internal ID (for reference/dedup) |
| `code` | string | Labour code (e.g. "E-001") |
| `labour_key` | string\|null | Short key identifier |
| `description` | string | Human-readable description |
| `group_name` | string | Category grouping (e.g. "ENGINE", "BRAKE SYSTEM") |
| `time_hours` | float | Standard labour time in hours |

### Verifying the Webhook Signature

**You MUST verify the signature before processing the webhook.** Here is the Python implementation:

```python
import hashlib
import hmac
import json
from odoo import http
from odoo.http import request

class RtsWebhookController(http.Controller):
    
    @http.route('/rts/labour-callback', type='http', auth='none',
                methods=['POST'], csrf=False)
    def labour_callback(self, **kwargs):
        # 1. Read raw body and signature header
        raw_body = request.httprequest.get_data(as_text=True)
        received_sig = request.httprequest.headers.get('X-RTS-Signature', '')
        
        # 2. Get webhook secret from system parameters
        ICP = request.env['ir.config_parameter'].sudo()
        webhook_secret = ICP.get_param('rts.webhook_secret', '')
        
        if not webhook_secret:
            return request.make_json_response(
                {'status': 'error', 'message': 'Webhook secret not configured'},
                status=500
            )
        
        # 3. Compute expected signature
        expected_sig = hmac.new(
            webhook_secret.encode('utf-8'),
            raw_body.encode('utf-8'),
            hashlib.sha256
        ).hexdigest()
        
        # 4. Compare (timing-safe)
        if not hmac.compare_digest(expected_sig, received_sig):
            return request.make_json_response(
                {'status': 'error', 'message': 'Invalid signature'},
                status=403
            )
        
        # 5. Parse and process
        data = json.loads(raw_body)
        job_order_id = int(data['job_order_id'])
        
        # 6. Find the job order
        RepairOrder = request.env['repair.order'].sudo()  # adjust model name
        order = RepairOrder.browse(job_order_id)
        if not order.exists():
            return request.make_json_response(
                {'status': 'error', 'message': 'Job order not found'},
                status=404
            )
        
        # 7. Create labour lines
        LabourLine = request.env['repair.rts.labour.line'].sudo()
        for labour in data.get('labours', []):
            LabourLine.create({
                'repair_order_id': order.id,
                'rts_labour_code_id': labour['rts_id'],
                'code': labour['code'],
                'labour_key': labour.get('labour_key', ''),
                'description': labour['description'],
                'group_name': labour.get('group_name', ''),
                'time_hours': labour.get('time_hours', 0.0),
            })
        
        return request.make_json_response({
            'status': 'success',
            'message': f"{len(data['labours'])} labour codes added to {order.name}",
        })
```

### Expected Response from Odoo

RTS expects a JSON response:

**Success (HTTP 200):**
```json
{
    "status": "success",
    "message": "3 labour codes added to JO-2026-0001"
}
```

**Error (HTTP 4xx/5xx):**
```json
{
    "status": "error",
    "message": "Description of what went wrong"
}
```

---

## Odoo Module Implementation

### Recommended Module Structure

```
odoo_rts_integration/
├── __init__.py
├── __manifest__.py
├── models/
│   ├── __init__.py
│   ├── repair_order.py          # Extend repair order with chassis + button
│   └── repair_rts_labour_line.py # Labour line model
├── controllers/
│   ├── __init__.py
│   └── webhook.py               # Webhook endpoint
├── views/
│   └── repair_order_views.xml    # Form view modifications
└── data/
    └── ir_config_parameter.xml   # Default config values
```

### Model: repair_rts_labour_line.py

```python
from odoo import models, fields

class RepairRtsLabourLine(models.Model):
    _name = 'repair.rts.labour.line'
    _description = 'RTS Labour Code Line'
    _order = 'group_name, code'

    repair_order_id = fields.Many2one(
        'repair.order', string='Repair Order',
        required=True, ondelete='cascade', index=True
    )
    rts_labour_code_id = fields.Integer(string='RTS Code ID')
    code = fields.Char(string='Labour Code', required=True)
    labour_key = fields.Char(string='Labour Key')
    description = fields.Char(string='Description')
    group_name = fields.Char(string='Group')
    time_hours = fields.Float(string='Standard Hours', digits=(8, 2))
    
    # Odoo-specific pricing fields (fill these as needed)
    price_unit = fields.Float(string='Unit Price')
    quantity = fields.Float(string='Qty', default=1.0)
    subtotal = fields.Float(string='Subtotal', compute='_compute_subtotal')
    
    def _compute_subtotal(self):
        for rec in self:
            rec.subtotal = rec.price_unit * rec.quantity
```

### Model: repair_order.py

```python
import hashlib
import hmac
import os
import time
import urllib.parse
from odoo import models, fields, api
from odoo.exceptions import UserError

class RepairOrder(models.Model):
    _inherit = 'repair.order'  # CHANGE THIS to your actual model

    chassis_no = fields.Char(string='Chassis Number', size=20)
    rts_model_prefix = fields.Char(string='RTS Model Prefix', readonly=True)
    rts_labour_line_ids = fields.One2many(
        'repair.rts.labour.line', 'repair_order_id',
        string='RTS Labour Lines'
    )
    rts_labour_count = fields.Integer(
        compute='_compute_rts_labour_count'
    )
    
    @api.depends('rts_labour_line_ids')
    def _compute_rts_labour_count(self):
        for rec in self:
            rec.rts_labour_count = len(rec.rts_labour_line_ids)
    
    def action_select_rts_labour(self):
        """Open RTS labour selection page in a new browser tab."""
        self.ensure_one()
        if not self.chassis_no or len(self.chassis_no) < 6:
            raise UserError(
                "Please enter a valid chassis number (min 6 characters) "
                "before selecting labour codes."
            )
        
        ICP = self.env['ir.config_parameter'].sudo()
        base_url = ICP.get_param('rts.base_url', '').rstrip('/')
        shared_secret = ICP.get_param('rts.shared_secret', '')
        
        if not base_url or not shared_secret:
            raise UserError(
                "RTS integration not configured. "
                "Please set rts.base_url and rts.shared_secret "
                "in System Parameters."
            )
        
        # Build callback URL
        odoo_base = self.env['ir.config_parameter'].sudo().get_param(
            'web.base.url', ''
        )
        callback_url = f"{odoo_base}/rts/labour-callback"
        
        customer_name = self.partner_id.name if self.partner_id else ''
        
        # Build signed URL
        params = {
            'job_order_id': str(self.id),
            'job_number': str(self.name or self.id),
            'chassis': str(self.chassis_no).upper(),
            'customer_name': str(customer_name),
            'callback_url': callback_url,
            'exp': str(int(time.time()) + 300),
            'nonce': hashlib.md5(os.urandom(16)).hexdigest(),
        }
        
        sorted_msg = '&'.join(
            f"{k}={v}" for k, v in sorted(params.items())
        )
        sig = hmac.new(
            shared_secret.encode('utf-8'),
            sorted_msg.encode('utf-8'),
            hashlib.sha256
        ).hexdigest()
        params['sig'] = sig
        
        url = f"{base_url}/odoo/select-labour?{urllib.parse.urlencode(params)}"
        
        # Update model prefix for display
        self.rts_model_prefix = self.chassis_no[:6].upper()
        
        return {
            'type': 'ir.actions.act_url',
            'url': url,
            'target': 'new',  # Opens in new tab
        }
```

### View: repair_order_views.xml

```xml
<?xml version="1.0" encoding="utf-8"?>
<odoo>
    <record id="view_repair_order_form_rts" model="ir.ui.view">
        <field name="name">repair.order.form.rts</field>
        <field name="model">repair.order</field>  <!-- CHANGE to your model -->
        <field name="inherit_id" ref="repair.view_repair_order_form"/>
        <field name="arch" type="xml">
            <!-- Add chassis field and button near the top of the form -->
            <xpath expr="//sheet" position="inside">
                <group string="RTS Labour Codes">
                    <group>
                        <field name="chassis_no"/>
                        <field name="rts_model_prefix"/>
                    </group>
                    <group>
                        <button name="action_select_rts_labour"
                                type="object"
                                string="Select Labour Codes from RTS"
                                class="btn-primary"
                                icon="fa-external-link"/>
                        <field name="rts_labour_count" string="Codes Added"/>
                    </group>
                </group>
                
                <!-- Labour lines tree view -->
                <notebook>
                    <page string="RTS Labour Lines" 
                          attrs="{'invisible': [('rts_labour_count', '=', 0)]}">
                        <field name="rts_labour_line_ids">
                            <tree editable="bottom">
                                <field name="code"/>
                                <field name="description"/>
                                <field name="group_name"/>
                                <field name="time_hours"/>
                                <field name="price_unit"/>
                                <field name="quantity"/>
                                <field name="subtotal"/>
                            </tree>
                        </field>
                    </page>
                </notebook>
            </xpath>
        </field>
    </record>
</odoo>
```

> **⚠️ NOTE:** The XML `xpath` expressions above assume the standard `repair.order` form. You will need to adjust the `inherit_id` and `xpath` expressions to match your actual custom model's form view.

---

## Service History Viewer

This feature allows Odoo users to view a vehicle's **complete service history** from the RTS Master Data Hub by entering a chassis number. Unlike the labour code selection flow, this is **read-only** — no webhook callback is needed.

### Architecture

```
┌─────────────────────┐         ┌─────────────────────┐
│      ODOO           │         │    RTS APP           │
│  (Your System)      │         │  (Master Data Hub)   │
│                     │         │                      │
│  1. User clicks     │         │                      │
│     "View History"  │         │                      │
│                     │  ──2──► │  3. Verify signature  │
│  Generate signed URL│  HTTPS  │  4. Lookup vehicle    │
│  Open in new tab    │         │  5. Show Interactive  │
│                     │         │     Master-Detail View│
│                     │         │     (Labour & Parts)  │
└─────────────────────┘         └─────────────────────┘
```

### URL Parameters (Simplified)

Since this is read-only, the signed URL only needs:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `chassis` | string | ✅ | Vehicle chassis number |
| `exp` | string | ✅ | Unix timestamp when URL expires |
| `nonce` | string | ✅ | Random unique string (prevents replay) |
| `sig` | string | ✅ | HMAC-SHA256 signature |

> **Note:** `callback_url`, `job_order_id`, and `job_number` are NOT required for this endpoint.

### Python Implementation (Odoo Side)

```python
def generate_rts_history_url(chassis_no):
    """
    Generate an HMAC-signed URL to view service history.
    
    Args:
        chassis_no: Vehicle chassis number (min 6 chars, ideally 17)
    
    Returns:
        str: Complete signed URL to open in browser
    """
    ICP = self.env['ir.config_parameter'].sudo()
    base_url = ICP.get_param('rts.base_url', '').rstrip('/')
    shared_secret = ICP.get_param('rts.shared_secret', '')
    
    params = {
        'chassis': str(chassis_no).upper(),
        'exp': str(int(time.time()) + 300),  # 5 minute expiry
        'nonce': hashlib.md5(os.urandom(16)).hexdigest(),
    }
    
    sorted_msg = '&'.join(f"{k}={v}" for k, v in sorted(params.items()))
    sig = hmac.new(
        shared_secret.encode('utf-8'),
        sorted_msg.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()
    params['sig'] = sig
    
    return f"{base_url}/odoo/service-history?{urllib.parse.urlencode(params)}"
```

### Odoo Module — Button on Vehicle/Job Order Form

```python
def action_view_service_history(self):
    """Open RTS service history viewer in a new browser tab."""
    self.ensure_one()
    if not self.chassis_no or len(self.chassis_no) < 6:
        raise UserError(
            "Please enter a valid chassis number (min 6 characters)."
        )
    url = self._generate_rts_history_url(self.chassis_no)
    return {
        'type': 'ir.actions.act_url',
        'url': url,
        'target': 'new',
    }
```

### Features

- **Master-Detail Layout:** A summary table shows all invoices. Clicking a row instantly displays the detailed labour and sparepart tables for that specific invoice in a side-by-side view.
- **Instant Keyword Search:** Users can search terms like "ban", "wiper", "oli". Searching is performed client-side (Alpine.js) for maximum performance and avoids signature expiration/403 errors.
- **Visual Highlighting:** Matching search terms are highlighted in yellow within descriptions.
- **CSV Export:** The currently viewed service history (including search filters) can be downloaded as a CSV.
- **Side-by-Side Comparison:** Labour details and Sparepart details are shown in a two-column grid for easier audit and parity with legacy reports.
- **Summary Dashboard:** Provides total service visits, total labour count, total parts count, and date range at the top.

### XML View

```xml
<button name="action_view_service_history"
        type="object"
        string="View Service History (RTS)"
        class="btn-secondary"
        icon="fa-history"/>
```

---

## Testing & Debugging

### Step 1: Verify Secrets Match

In RTS, the secrets are in `.env`:
```
ODOO_SHARED_SECRET=your-shared-secret
ODOO_WEBHOOK_SECRET=your-webhook-secret
```

In Odoo System Parameters:
```
rts.shared_secret = your-shared-secret      (must match ODOO_SHARED_SECRET)
rts.webhook_secret = your-webhook-secret     (must match ODOO_WEBHOOK_SECRET)
```

### Step 2: Test URL Generation

Generate a URL from Odoo and manually open it in a browser. You should see the labour code selection page. If you see an error:

| Error | Cause | Fix |
|---|---|---|
| "This link has expired" | `exp` timestamp is in the past | Check server time sync (NTP), increase expiry |
| "Invalid signature" | Secret mismatch or param encoding issue | Verify secrets match exactly, check URL encoding |
| "This link has already been used" | Nonce was replayed | Generate a new URL (each click = new URL) |
| "Odoo integration is not configured" | RTS missing `.env` values | Set `ODOO_SHARED_SECRET` in RTS `.env` |

### Step 3: Test Webhook Manually

Use `curl` to simulate what RTS sends to your webhook:

```bash
# Set your webhook secret
SECRET="your-webhook-secret"

# Create test payload
PAYLOAD='{"job_order_id":"42","source":"rts_labour_app","timestamp":"2026-05-01T17:30:00+07:00","labours":[{"rts_id":10,"code":"E-001","labour_key":"ENG01","description":"Ganti oli mesin","group_name":"ENGINE","time_hours":0.5}]}'

# Compute signature
SIG=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')

# Send to Odoo
curl -X POST https://odoo.yourdomain.com/rts/labour-callback \
  -H "Content-Type: application/json" \
  -H "X-RTS-Signature: $SIG" \
  -H "X-RTS-Timestamp: $(date +%s)" \
  -d "$PAYLOAD"
```

### Step 4: Check RTS Logs

RTS logs all security events. Ask the RTS admin to check:
- **API access logs** — visible in the RTS admin panel under Security & Logs → API Logs
- **Security channel** — `storage/logs/security.log` for signature failures

---

## Error Handling

### Common Errors and Solutions

| Scenario | What Happens | How to Handle |
|---|---|---|
| RTS app is down | URL won't load | Show user-friendly error, retry later |
| Webhook fails | RTS shows error page | User can retry submission from RTS page |
| Signature mismatch | 403 error | Verify secrets match, check encoding |
| Job order not found | 404 from webhook | Verify `job_order_id` is correct record ID |
| Duplicate submission | Codes added twice | Use `rts_labour_code_id` to deduplicate |

### Deduplication

To prevent duplicate labour lines if the user submits twice, add this check in your webhook handler:

```python
# Before creating, check if this RTS code already exists on this order
existing = LabourLine.search([
    ('repair_order_id', '=', order.id),
    ('rts_labour_code_id', '=', labour['rts_id']),
])
if not existing:
    LabourLine.create({...})
```

---

## API Reference

### RTS Selection Page

| | |
|---|---|
| **URL** | `GET {rts_base_url}/odoo/select-labour` |
| **Auth** | HMAC-signed URL parameters |
| **Opens** | Browser page with labour code checkboxes |

### Webhook Callback

| | |
|---|---|
| **URL** | `POST {odoo_base_url}/rts/labour-callback` |
| **Auth** | `X-RTS-Signature` header (HMAC-SHA256 of body) |
| **Content-Type** | `application/json` |
| **Body** | See [Request Body](#request-body) section above |
| **Response** | JSON with `status` and `message` fields |

---

## Troubleshooting: Clock Drift

When the Odoo mock app (or real Odoo) and RTS run in Docker containers on **different hosts**, their system clocks may drift apart. This causes signed URLs to be rejected as "Expired" even with generous expiry windows.

### Root Cause

- `time.time()` in Python and `time()` in PHP both return the kernel's real-time clock
- Docker containers inherit the host's clock — they have NO independent time source
- If hosts A and B are not NTP-synchronized, their clocks drift
- The URL `exp` parameter is generated on host A but verified on host B

### Built-in Fixes

RTS includes two protections against clock drift:

1. **Clock skew tolerance** (`ODOO_SKEW_TOLERANCE_SECONDS`, default 30s) — allows the receiver's clock to be slightly ahead of the sender's
2. **UTC enforcement** (`TZ=UTC`) — ensures both containers use UTC for time calculations

### Diagnosing Clock Drift

Run the diagnostic script on **both** Docker hosts:

```bash
# On the Odoo host (mock or real)
bash scripts/check_ntp_sync.sh

# On the RTS host
bash scripts/check_ntp_sync.sh
```

Compare the `Current UTC timestamp` values. They should differ by no more than a few seconds.

### Fixing NTP Sync

If the script reports "NOT synchronized":

```bash
# Check current status
timedatectl status

# Enable NTP
sudo timedatectl set-ntp true

# Install chrony if not present (most reliable)
sudo apt install -y chrony      # Debian/Ubuntu
sudo yum install -y chrony      # RHEL/CentOS

# Enable and start
sudo systemctl enable --now chronyd
```

### Verifying the Fix

After NTP is enabled, verify sync is working:

```bash
chronyc tracking | grep -E "Stratum|System time"
# Should show "System time: 0.000000xxx seconds slow/fast of NTP time"
```

Then redeploy both containers and test the integration.

---

## Appendix: Quick Reference Card

```
ODOO SIDE:
  1. System Parameters to set:
     - rts.base_url         → https://rts.yourdomain.com
     - rts.shared_secret    → (64-char hex, matches RTS ODOO_SHARED_SECRET)
     - rts.webhook_secret   → (64-char hex, matches RTS ODOO_WEBHOOK_SECRET)
  
  2. New model: repair.rts.labour.line (One2many on repair order)
  
  3. Button on form: calls action_select_rts_labour() → opens signed URL
  
  4. Webhook endpoint: POST /rts/labour-callback → verifies sig, creates lines

  5. Service History: calls action_view_service_history() → opens signed URL (read-only)

RTS SIDE (already implemented):
  - GET  /odoo/select-labour    → shows labour selection UI
  - POST /odoo/select-labour    → sends selected codes to Odoo callback
  - GET  /odoo/service-history  → shows service history viewer (read-only)
```
