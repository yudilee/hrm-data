import os
import sys
import glob
import re
import pandas as pd
import mysql.connector
import json
from datetime import datetime
from tqdm import tqdm
from decimal import Decimal

PLACEHOLDER_PHONES = {'0', '-', 'n/a', 'nan', '00000000', 'none', ''}
EMAIL_RE = re.compile(r'^[^@]+@[^@]+\.[^@]+$')

def validate_email(email):
    """Returns True if email looks valid, False otherwise."""
    if not email or not str(email).strip():
        return True  # empty is OK, not invalid
    return bool(EMAIL_RE.match(str(email).strip()))

def validate_phone(phone):
    """Returns True if phone is a real number (not a placeholder)."""
    if not phone:
        return True
    return str(phone).strip().lower() not in PLACEHOLDER_PHONES

# Connection settings
DB_HOST = os.environ.get('DB_HOST', '127.0.0.1')
DB_PORT = os.environ.get('DB_PORT', '3306')
DB_USER = os.environ.get('DB_USERNAME', 'sail')
DB_PASSWORD = os.environ.get('DB_PASSWORD', 'password')
DB_NAME = os.environ.get('DB_DATABASE', 'rts_labour_app')

SOURCE_DIR = "/home/yudi/dev/rts_code/data cust/"

def normalize_name(name):
    if pd.isna(name) or not str(name).strip():
        return ''
    n = str(name).strip().upper()
    n = re.sub(r'^(MR|MRS)\.?\s+', '', n, flags=re.IGNORECASE)
    n = re.sub(r'[.,\s]', '', n)
    return n

def canonical_phone(phone):
    if pd.isna(phone):
        return None
    p = str(phone)
    if p in ['0', '-', 'N/A', 'n/a', 'nan']:
        return None
    
    p = re.sub(r'[^0-9+]', '', p)
    if p.startswith('+62'):
        p = '0' + p[3:]
    elif p.startswith('62') and len(p) > 9:
        p = '0' + p[2:]
        
    return p if p else None

def detect_phone_type(phone):
    p = canonical_phone(phone)
    if not p:
        return 'unknown'
    if p.startswith('08'):
        return 'mobile'
    if re.match(r'^0[2-9][0-9]', p):
        return 'landline'
    return 'unknown'

def clean_str(val):
    if pd.isna(val) or val == 'nan' or val == '':
        return None
    v = str(val).strip()
    return v if v else None

def calculate_quality_score(row):
    score = 0
    if clean_str(row.get('name')): score += 20
    if clean_str(row.get('telp_1')) or clean_str(row.get('telp_2')): score += 20
    if clean_str(row.get('email')): score += 15
    if clean_str(row.get('address_1')): score += 15
    if clean_str(row.get('company_name')): score += 10
    if clean_str(row.get('dept')): score += 10
    if clean_str(row.get('title')): score += 10
    return min(100, score)

def connect_db():
    return mysql.connector.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASSWORD,
        database=DB_NAME
    )

def main():
    print("--- Start DMS Customer Import ---")
    files = glob.glob(os.path.join(SOURCE_DIR, "*.xls"))
    
    if not files:
        print(f"No XLS files found in {SOURCE_DIR}")
        return

    # 1. Read all data into memory
    print(f"Reading {len(files)} files...")
    all_data = []
    
    for f in files:
        # Robust: extract branch token before (H04) regardless of date in filename
        # Handles: 'DATA CUST PER 18 APR26 SBY CV(H04).xls', 'DATA CUST PER 21 APR26 DPS PC(H04).xls', etc.
        m = re.search(r'([A-Z]{2,4}\s+(?:PC|CV))\s*\(H04\)', os.path.basename(f), re.IGNORECASE)
        branch_token = m.group(1).upper().strip() if m else 'UNKNOWN'
        source_code  = 'HRM' + branch_token  # e.g. 'HRMSBY CV'
        print(f"Processing branch: {source_code} from {os.path.basename(f)}")
        try:
            df = pd.read_excel(f, dtype=str)
            
            # Map column names to standard keys based on expected format
            for _, row in df.iterrows():
                magic_cust = clean_str(row.get('Magic cust', row.get('magic_cust')))
                if not magic_cust: continue
                
                name = clean_str(row.get('Nama Customer', row.get('nama_customer')))
                title = clean_str(row.get('Title', row.get('title')))
                
                is_company = False
                if title:
                    t_upper = title.upper()
                    if t_upper in ['PT', 'CV', 'PO', 'UD', 'NV', 'TBK']:
                        is_company = True
                        
                c_type = 'company' if is_company else 'individual'
                
                norm_name = normalize_name(name)
                phone1 = clean_str(row.get('Telp 01', row.get('telp_01')))
                phone2 = clean_str(row.get('Telp 02', row.get('telp_02')))
                
                fingerprint = canonical_phone(phone1)
                if not fingerprint:
                    fingerprint = canonical_phone(phone2)
                    
                p_type = detect_phone_type(phone1 if canonical_phone(phone1) else phone2)

                record = {
                    'magic_cust': int(magic_cust),
                    'source': source_code,
                    'name': name,
                    'normalized_name': norm_name,
                    'is_company': is_company,
                    'customer_type': c_type,
                    'phone_fingerprint': fingerprint,
                    'primary_phone_type': p_type,
                    'address_1': clean_str(row.get('ADDRESS 1', row.get('address_1'))),
                    'address_2': clean_str(row.get('ADDRESS 2', row.get('address_2'))),
                    'address_3': clean_str(row.get('ADDRESS 3', row.get('address_3'))),
                    'address_4': clean_str(row.get('ADDRESS 4', row.get('address_4'))),
                    'address_5': clean_str(row.get('ADDRESS 5', row.get('address_5'))),
                    'company_name': clean_str(row.get('Company name', row.get('company_name'))),
                    'magic_comp': int(clean_str(row.get('Magic comp', row.get('magic_comp'))) or 0),
                    'email': clean_str(row.get('E-mail address', row.get('e_mail_address'))),
                    'dept': clean_str(row.get('Dept', row.get('dept'))),
                    'title': title,
                    'telp_1': phone1,
                    'telp_2': phone2,
                    'telp_3': clean_str(row.get('Telp 03', row.get('telp_03'))),
                    'telp_4': clean_str(row.get('Telp 04', row.get('telp_04'))),
                    'date_created': clean_str(row.get('Date created', row.get('date_created'))),
                }
                
                # Full address
                addrs = [record[f'address_{i}'] for i in range(1, 6) if record[f'address_{i}']]
                record['full_address'] = ', '.join(addrs) if addrs else None
                
                record['data_quality_score'] = calculate_quality_score(record)
                all_data.append(record)
        except Exception as e:
            print(f"Error reading {f}: {e}")

    print(f"Total raw records read: {len(all_data)}")

    # 2. Dedup and Merge
    print("Deduplicating and merging records...")
    merged_data = {}
    
    for r in all_data:
        # Avoid merging if both name and phone are empty. 
        # Use (source, magic_cust) as a unique key for these anonymous records.
        if not r['normalized_name'] and not r['phone_fingerprint']:
            key = (r['source'], r['magic_cust'], 'anonymous_collision_fix')
        else:
            key = (r['normalized_name'], r['phone_fingerprint'])
        
        legacy_entry = {'branch': r['source'], 'magic': r['magic_cust']}
        
        if key not in merged_data:
            r['legacy_mappings'] = [legacy_entry]
            merged_data[key] = r
        else:
            existing = merged_data[key]
            # Add mapping
            if legacy_entry not in existing['legacy_mappings']:
                existing['legacy_mappings'].append(legacy_entry)
                
            # Merge fields (prefer existing if not null, otherwise take new)
            for field in ['name', 'address_1', 'address_2', 'address_3', 'address_4', 'address_5', 
                          'full_address', 'company_name', 'email', 'dept', 'title', 
                          'telp_1', 'telp_2', 'telp_3', 'telp_4', 'date_created']:
                if not existing[field] and r[field]:
                    existing[field] = r[field]
            
            # Recalculate score after merge
            existing['data_quality_score'] = calculate_quality_score(existing)

    final_records = list(merged_data.values())
    print(f"Unique master customers after dedup: {len(final_records)}")

    # Compute sources for each record from its legacy_mappings
    for r in final_records:
        branches = sorted(set(m['branch'] for m in r['legacy_mappings'] if m.get('branch')))
        r['sources'] = json.dumps(branches)

    # 3. Database Insertion
    print("Connecting to database...")
    db = connect_db()
    cursor = db.cursor()

    insert_query = """
    INSERT INTO master_customers (
        name, normalized_name, is_company, customer_type, phone_fingerprint, primary_phone_type,
        address_1, address_2, address_3, address_4, address_5, full_address,
        company_name, magic_comp, email, dept, title,
        telp_1, telp_2, telp_3, telp_4, date_created, source, sources, legacy_mappings, data_quality_score,
        created_at, updated_at
    ) VALUES (
        %(name)s, %(normalized_name)s, %(is_company)s, %(customer_type)s, %(phone_fingerprint)s, %(primary_phone_type)s,
        %(address_1)s, %(address_2)s, %(address_3)s, %(address_4)s, %(address_5)s, %(full_address)s,
        %(company_name)s, %(magic_comp)s, %(email)s, %(dept)s, %(title)s,
        %(telp_1)s, %(telp_2)s, %(telp_3)s, %(telp_4)s, %(parsed_date)s, %(source)s, %(sources)s, %(legacy_mappings_json)s, %(data_quality_score)s,
        NOW(), NOW()
    )
    """

    # Pre-insertion validation pass
    print("Running pre-insertion validation...")
    invalid_emails     = 0
    placeholder_phones = 0
    for r in final_records:
        if not validate_email(r.get('email')):
            invalid_emails += 1
            print(f"  [WARN] Invalid email for '{r.get('name')}': {r.get('email')}")
        if not validate_phone(r.get('telp_1')):
            placeholder_phones += 1

    print(f"  Validation: {invalid_emails} invalid email(s), {placeholder_phones} placeholder phone(s).")

    print("Inserting into database...")
    inserted  = 0
    failed    = 0
    for r in tqdm(final_records):
        try:
            # Parse date safely
            parsed_date = None
            if r['date_created']:
                try:
                    if len(str(r['date_created'])) > 10:
                        parsed_date = datetime.strptime(str(r['date_created'])[:10], "%Y-%m-%d").strftime("%Y-%m-%d")
                    else:
                        parsed_date = datetime.strptime(str(r['date_created']), "%d/%m/%Y").strftime("%Y-%m-%d")
                except:
                    pass

            r['parsed_date'] = parsed_date
            r['legacy_mappings_json'] = json.dumps(r['legacy_mappings'])

            # create a copy without the list to avoid mysql-connector-python error
            query_params = {k: v for k, v in r.items() if k != 'legacy_mappings'}

            cursor.execute(insert_query, query_params)
            inserted += 1

            if inserted % 1000 == 0:
                db.commit()

        except Exception as e:
            failed += 1
            print(f"  [ERROR] Failed to insert '{r.get('name')}': {e}")
            db.rollback()

    db.commit()
    cursor.close()
    db.close()

    # ── Final Summary ─────────────────────────────────────────────────────────
    print("")
    print("=" * 50)
    print("  IMPORT SUMMARY")
    print("=" * 50)
    print(f"  Raw records read      : {len(all_data)}")
    print(f"  After deduplication   : {len(final_records)}")
    print(f"  Successfully inserted : {inserted}")
    print(f"  Failed (DB errors)    : {failed}")
    print(f"  Invalid emails (warn) : {invalid_emails}")
    print(f"  Placeholder phones    : {placeholder_phones}")
    print("=" * 50)
    print("Process completed.")

if __name__ == "__main__":
    main()
