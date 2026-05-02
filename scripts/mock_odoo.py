import hashlib
import hmac
import json
import time
import uuid
from flask import Flask, request, jsonify, render_template_string

app = Flask(__name__)

# --- CONFIGURATION (Match these with your RTS settings) ---
# PRODUCTION: https://hrm-data.hartonomotor-group.com
# LOCAL: http://localhost:8083
RTS_BASE_URL = "https://hrm-data.hartonomotor-group.com" 

SHARED_SECRET = "ffa05c883542271e5e8447ce86f6a887296ff673e9301835d2103b98cbbd365c"
WEBHOOK_SECRET = "d76c08b8a91b29fbdd4e12e3dceb51ccaf296b44f4bb517f895aca15abc54a6c"

# This is where RTS will send the data back to
MOCK_ODOO_URL = "http://localhost:5000/rts/labour-callback"
# ------------------------------------------------------

HTML_TEMPLATE = """
<!DOCTYPE html>
<html>
<head>
    <title>Mock Odoo ERP</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f0f2f5; color: #333; }
        .card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e1e4e8; }
        h1 { color: #714B67; margin-bottom: 30px; border-bottom: 2px solid #714B67; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
        .btn { background: #714B67; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold; border: none; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #5a3c52; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 14px; border-left: 5px solid #2ecc71; }
        .status { color: #2ecc71; font-weight: bold; }
        .info { background: #e8f4fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 25px; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Odoo Mock - Repair Module</h1>
        
        <div class="info">
            <strong>Testing Tip:</strong> Use a real Chassis Number (like WDD176...) or at least 6 characters that match your Labour Codes model prefix to see data.
        </div>

        <form action="/generate-url" method="POST">
            <div class="form-group">
                <label for="chassis">Vehicle Chassis Number:</label>
                <input type="text" id="chassis" name="chassis" value="WDD17604423456789" placeholder="e.g. WDD176...">
            </div>
            
            <div class="form-group">
                <label for="job_order">Job Order ID:</label>
                <input type="text" id="job_order" name="job_order" value="JO-12345">
            </div>

            <button type="submit" class="btn">Select Labour Codes in RTS</button>
        </form>
        
        {% if received_data %}
        <div id="result" style="margin-top: 40px;">
            <h3>Webhook Received! <span class="status">✅ Verified Signature</span></h3>
            <p>RTS just returned the following selection:</p>
            <pre>{{ received_data | tojson(indent=2) }}</pre>
        </div>
        {% endif %}
    </div>
</body>
</html>
"""

# Global variable to store last received data for demonstration
last_received_data = None

@app.route("/")
def index():
    global last_received_data
    return render_template_string(HTML_TEMPLATE, received_data=last_received_data)

@app.route("/generate-url", methods=["POST"])
def generate_url():
    chassis = request.form.get("chassis", "")
    job_order = request.form.get("job_order", "JO-12345")

    # 1. Prepare parameters
    params = {
        "job_order_id": job_order,
        "job_number": job_order,
        "chassis": chassis,
        "customer_name": "John Doe",
        "callback_url": MOCK_ODOO_URL,
        "nonce": uuid.uuid4().hex,
        "exp": str(int(time.time()) + 300) # 5 minutes expiry
    }

    # 2. Build canonical string (sorted keys)
    canonical = "&".join(f"{k}={params[k]}" for k in sorted(params.keys()))
    
    # 3. Sign
    signature = hmac.new(
        SHARED_SECRET.encode(),
        canonical.encode(),
        hashlib.sha256
    ).hexdigest()

    # 4. Final URL
    signed_url = f"{RTS_BASE_URL}/odoo/select-labour?{canonical}&sig={signature}"

    # In a real Odoo app, we would redirect. Here we just redirect the browser.
    from flask import redirect
    return redirect(signed_url)

@app.route("/rts/labour-callback", methods=["POST"])
def webhook():
    global last_received_data
    
    # 1. Get Signature header
    signature = request.headers.get("X-RTS-Signature")
    payload = request.data # raw bytes
    
    # 2. Verify
    expected_sig = hmac.new(
        WEBHOOK_SECRET.encode(),
        payload,
        hashlib.sha256
    ).hexdigest()

    if not hmac.compare_digest(signature or "", expected_sig):
        return jsonify({"status": "error", "message": "Invalid signature"}), 403

    # 3. Process
    last_received_data = request.json
    print("\\n[MOCK ODOO] Webhook Received Successfully!")
    print(json.dumps(last_received_data, indent=2))
    
    return jsonify({"status": "success", "message": "Labour codes imported into Odoo"})

if __name__ == "__main__":
    print("Mock Odoo running on http://localhost:5000")
    print(f"RTS Target: {RTS_BASE_URL}")
    app.run(port=5000, debug=True)
