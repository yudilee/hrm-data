#!/usr/bin/env python3
import sys
import os
import pymysql
from dbfread import DBF
from datetime import datetime

# DB Config (from .env / host perspective)
DB_HOST     = '127.0.0.1'
DB_PORT     = 3309
DB_NAME     = 'rts_labour_app'
DB_USER     = 'sail'
DB_PASSWORD = 'password'

DBF_PATH = '/home/yudi/dev/rts_code/supplier/supplier.DBF'

def clean(val):
    if val is None: return None
    s = str(val).strip()
    return s if s else None

def main():
    if not os.path.exists(DBF_PATH):
        print(f"Error: {DBF_PATH} not found.")
        sys.exit(1)

    print(f"Opening {DBF_PATH}...")
    table = DBF(DBF_PATH, encoding='iso-8859-1')
    
    conn = pymysql.connect(
        host=DB_HOST, port=DB_PORT, user=DB_USER, 
        password=DB_PASSWORD, database=DB_NAME,
        charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor
    )

    try:
        with conn.cursor() as cursor:
            # Clear existing history-imported suppliers if needed? 
            # User might want to refresh. Let's use INSERT IGNORE or REPLACE.
            
            count = 0
            for record in table:
                code = clean(record.get('CCODESUP'))
                if not code: continue

                name = clean(record.get('ENAMESUP'))
                addr1 = clean(record.get('EADDRSUP1'))
                addr2 = clean(record.get('EADDRSUP2'))
                city = clean(record.get('ECITY'))
                zip_code = clean(record.get('KODEPOS'))
                phone = clean(record.get('EPHONSUP'))
                fax = clean(record.get('EFAXSUP'))
                contact = clean(record.get('ECONTSUP'))
                email = clean(record.get('EMAIL'))
                bank = clean(record.get('BANK'))
                acc_no = clean(record.get('ACC_NO'))
                acc_name = clean(record.get('ACC_NAME'))
                cat = clean(record.get('MSUPPLIER'))

                sql = """
                    INSERT INTO master_suppliers 
                    (code, name, address_1, address_2, city, postal_code, phone, fax, 
                     contact_person, email, bank_name, bank_account_no, bank_account_name, 
                     category, sync_status, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'pending', NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        name=VALUES(name), address_1=VALUES(address_1), address_2=VALUES(address_2),
                        city=VALUES(city), postal_code=VALUES(postal_code), phone=VALUES(phone),
                        fax=VALUES(fax), contact_person=VALUES(contact_person), email=VALUES(email),
                        bank_name=VALUES(bank_name), bank_account_no=VALUES(bank_account_no),
                        bank_account_name=VALUES(bank_account_name), category=VALUES(category),
                        updated_at=NOW()
                """
                cursor.execute(sql, (
                    code, name, addr1, addr2, city, zip_code, phone, fax,
                    contact, email, bank, acc_no, acc_name, cat
                ))
                count += 1
                if count % 100 == 0:
                    print(f"Imported {count} suppliers...")

            conn.commit()
            print(f"Finished! Total {count} suppliers imported/updated.")

    finally:
        conn.close()

if __name__ == "__main__":
    main()
