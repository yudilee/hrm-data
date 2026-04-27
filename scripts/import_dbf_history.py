import os
import mysql.connector
import dbfread.field_parser 
from dbfread import DBF
from datetime import date, datetime
import decimal
from tqdm import tqdm

# MONKEY PATCH: dbfread.FieldParser.parseN
original_parseN = dbfread.field_parser.FieldParser.parseN
def robust_parseN(self, field, data):
    try:
        clean_data = data.replace(b'\x00', b'').strip()
        if not clean_data: return 0
        try:
            return int(clean_data)
        except ValueError:
            return float(clean_data.replace(b',', b'.'))
    except Exception:
        return 0
dbfread.field_parser.FieldParser.parseN = robust_parseN

# Environment Awareness
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = int(os.getenv('DB_PORT', '3306'))

if DB_HOST == '127.0.0.1' and DB_PORT == 3306:
    DB_PORT = 3309 

DB_CONFIG = {
    'host': DB_HOST,
    'port': DB_PORT,
    'user': os.getenv('DB_USERNAME', 'sail'),
    'password': os.getenv('DB_PASSWORD', 'password'),
    'database': os.getenv('DB_DATABASE', 'rts_labour_app')
}

def clean_str(val):
    if val is None: return None
    return str(val).strip()

def clean_num(val):
    if val is None: return 0
    try: return float(val)
    except: return 0

def clean_date(val):
    if isinstance(val, (date, datetime)):
        year = val.year
        # Normalise corrupted FoxPro years (e.g. 9019 -> 2019)
        if year > 2050 or (year < 1970 and year > 0):
            try:
                val = val.replace(year=2000 + (year % 100))
            except ValueError:
                pass # Keep original if leap year edge case fails
        return val.strftime('%Y-%m-%d')
    return None

def truncate_all_tables(conn):
    cursor = conn.cursor()
    print("Truncating all history tables for fresh start...", flush=True)
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
    cursor.execute("TRUNCATE TABLE service_history_parts")
    cursor.execute("TRUNCATE TABLE service_history_labours")
    cursor.execute("TRUNCATE TABLE service_histories")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
    conn.commit()
    cursor.close()

def import_wsdata(conn, db_path, source):
    cursor = conn.cursor()
    if not os.path.exists(db_path):
        return {}

    db = DBF(db_path, load=False, char_decode_errors='ignore')
    total_records = len(db)
    
    sql = """INSERT INTO service_histories 
             (CJOBN, CINVN, CNPOL, CHASN, CENGN, DRECV, DINVN, CCUST, ENAME, EADDR, ECITY, EPHON, ETYPE, DSTNK, EKMPOS, ALBRS, ASPTS, ASSPS, ASUBS, AOTHS1, AOTHS2, DISC, ATAXS, AMTRS, PTAX, source, created_at, updated_at) 
             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""
    
    batch = []
    count = 0
    
    with tqdm(total=total_records, desc=f"[{source}] WSDATA", unit="rec") as pbar:
        for r in db:
            cjobn = clean_str(r.get('CJOBN'))
            if not cjobn: 
                pbar.update(1)
                continue
            vals = (
                cjobn, clean_str(r.get('CINVN')), clean_str(r.get('CNPOL')), clean_str(r.get('CHASN')), 
                clean_str(r.get('CENGN')), clean_date(r.get('DRECV')), clean_date(r.get('DINVN')), 
                clean_str(r.get('CCUST')), clean_str(r.get('ENAME')), clean_str(r.get('EADDR')), 
                clean_str(r.get('ECITY')), clean_str(r.get('EPHON')), clean_str(r.get('ETYPE')), 
                clean_date(r.get('DSTNK')), clean_num(r.get('EKMPOS')), clean_num(r.get('ALBRS')), 
                clean_num(r.get('ASPTS')), clean_num(r.get('ASSPS')), clean_num(r.get('ASUBS')), 
                clean_num(r.get('AOTHS1')), clean_num(r.get('AOTHS2')), clean_num(r.get('DISC')), 
                clean_num(r.get('ATAXS')), clean_num(r.get('AMTRS')), clean_num(r.get('PTAX')),
                source
            )
            batch.append(vals)
            count += 1
            if len(batch) >= 1000:
                cursor.executemany(sql, batch)
                conn.commit()
                batch = []
            pbar.update(1)
            
        if batch:
            cursor.executemany(sql, batch)
            conn.commit()
    
    header_mapping = {}
    cursor.execute("SELECT id, CJOBN, CINVN, DINVN FROM service_histories WHERE source = %s", (source,))
    for (hid, cj, ci, di) in cursor:
        distr = di.strftime('%Y-%m-%d') if di else None
        key = (clean_str(cj), clean_str(ci), distr)
        header_mapping[key] = hid
        
    cursor.close()
    return header_mapping

def import_wsdopr(conn, db_path, source, header_mapping):
    if not os.path.exists(db_path) or not header_mapping:
        return

    cursor = conn.cursor()
    db = DBF(db_path, load=False, char_decode_errors='ignore')
    total_records = len(db)
    
    sql = """INSERT INTO service_history_labours (service_history_id, CJOBN, CINVN, CDJOB, EMJOB, QHOUR, TAKEN, NET, DISC, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""
    batch = []
    count = 0
    
    with tqdm(total=total_records, desc=f"[{source}] WSDOPR", unit="rec") as pbar:
        for r in db:
            try:
                cjobn, cinvn, dinvn = clean_str(r.get('CJOBN')), clean_str(r.get('CINVN')), clean_date(r.get('DINVN'))
                header_id = header_mapping.get((cjobn, cinvn, dinvn))
                if not header_id: 
                    pbar.update(1)
                    continue
                
                vals = (header_id, cjobn, cinvn, clean_str(r.get('CDJOB')), clean_str(r.get('EMJOB')), clean_num(r.get('QHOUR')), clean_num(r.get('TAKEN')), clean_num(r.get('NET')), clean_num(r.get('DISC')))
                batch.append(vals)
                count += 1
                if len(batch) >= 5000:
                    cursor.executemany(sql, batch)
                    conn.commit()
                    batch = []
            except: pass
            pbar.update(1)
            
        if batch:
            cursor.executemany(sql, batch)
            conn.commit()
    cursor.close()

def import_etd250(conn, db_path, source, header_mapping):
    if not os.path.exists(db_path) or not header_mapping:
        return

    cursor = conn.cursor()
    db = DBF(db_path, load=False, char_decode_errors='ignore')
    total_records = len(db)
    
    sql = """INSERT INTO service_history_parts (service_history_id, CJOBN, CINVN, CVCHR, CPART, EDESC, QRECV, ASPPRC, AFIFO, ADISCG, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())"""
    batch = []
    count = 0
    
    with tqdm(total=total_records, desc=f"[{source}] ETD250", unit="rec") as pbar:
        for r in db:
            try:
                cjobn, cinvn, dinvn = clean_str(r.get('CJOBN')), clean_str(r.get('CINVN')), clean_date(r.get('DINVN'))
                header_id = header_mapping.get((cjobn, cinvn, dinvn))
                if not header_id: 
                    pbar.update(1)
                    continue

                vals = (header_id, cjobn, cinvn, clean_str(r.get('CVCHR')), clean_str(r.get('CPART')), clean_str(r.get('EDESC')), clean_num(r.get('QRECV')), clean_num(r.get('ASPPRC')), clean_num(r.get('AFIFO')), clean_num(r.get('ADISCG')))
                batch.append(vals)
                count += 1
                if len(batch) >= 5000:
                    cursor.executemany(sql, batch)
                    conn.commit()
                    batch = []
            except: pass
            pbar.update(1)
            
        if batch:
            cursor.executemany(sql, batch)
            conn.commit()
    cursor.close()

def main():
    conn = mysql.connector.connect(**DB_CONFIG)
    try:
        base_path = "/var/www/html/vehicle history/"
        if not os.path.exists(base_path):
             base_path = "/home/yudi/dev/rts_code/vehicle history/"

        print(f"Syncing from {base_path}...", flush=True)
        
        truncate_all_tables(conn)

        branches = [d for d in os.listdir(base_path) if os.path.isdir(os.path.join(base_path, d))]
        print(f"Found {len(branches)} branch folders.", flush=True)

        for branch in branches:
            branch_path = os.path.join(base_path, branch)
            files = os.listdir(branch_path)
            wsdata = next((f for f in files if f.lower() == 'wsdata.dbf'), None)
            wsdopr = next((f for f in files if f.lower() == 'wsdopr.dbf'), None)
            etd250 = next((f for f in files if f.lower() == 'etd250.dbf'), None)

            if wsdata:
                header_map = import_wsdata(conn, os.path.join(branch_path, wsdata), branch)
                if wsdopr:
                    import_wsdopr(conn, os.path.join(branch_path, wsdopr), branch, header_map)
                if etd250:
                    import_etd250(conn, os.path.join(branch_path, etd250), branch, header_map)
            else:
                print(f"[{branch}] No WSDATA.DBF found, skipping.", flush=True)

        print("Import completely successfully.", flush=True)
    finally:
        conn.close()

if __name__ == "__main__":
    main()
