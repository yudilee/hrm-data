# Odoo Integration Update — v1.1
**Date:** May 4, 2026
**Subject:** Improvements to Service History Viewer & Search

## Summary of Changes
We have updated the **Service History Viewer** (the page opened from Odoo to view vehicle history) to improve usability and fix a search-related security issue.

### 1. New Master-Detail UI
The view has been upgraded from expandable cards to a professional **Master-Detail Table** layout:
- **Top Table:** Shows a summary of all service invoices (WIP No, Branch, Invoice Date, Totals, etc.).
- **Bottom Detail:** Clicking any row in the top table instantly updates the Labour and Sparepart detail tables below.
- **Side-by-Side View:** Labour and Spareparts are now displayed side-by-side on wide screens for easier cross-referencing.

### 2. Instant Client-Side Search (Fixes 403 Errors)
Previously, using the search bar would reload the page and add a `?search=` parameter to the URL, which frequently caused **"403 Invalid Signature"** errors because the new parameter wasn't part of the original Odoo signature.
- **Change:** Searching is now performed entirely on the client-side (using Alpine.js).
- **Benefit:** Search is instant, doesn't reload the page, and **never breaks the HMAC signature**.
- **Highlighting:** Matching text in labour/part descriptions is highlighted in real-time.

### 3. Improved Data Mapping
Columns in the summary table now include:
- **WIP NO.** (`CJOBN`)
- **BRANCH** (`source`)
- **POLICE NO.** (`CNPOL`)
- **CHASSIS NO.** (`CHASN`)
- **RECEIVE DATE** (`DRECV`)
- **INVOICE / DATE** (`CINVN` / `DINVN`)
- **TOTALS** (Labour, Part, Sublet, Others, Tax, Total Amount)
- **KM POS.** (`EKMPOS`)

## Actions for Odoo Team
- **No code changes are required on the Odoo side.** The existing signed URL logic remains compatible.
- **Testing:** Please verify the new layout by clicking the "View Service History" button in Odoo/Mock App.
- **Deployment:** The changes are live in the latest production build of the RTS Data Hub.

---
*For technical details, see the full ODOO_INTEGRATION_GUIDE.md in the docs folder.*
