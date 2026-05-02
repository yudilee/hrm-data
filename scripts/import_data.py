import os
import pandas as pd
import mysql.connector
import math
import re

# Configuration
DIRECTORY = os.environ.get('DATA_DIR', '/var/www/html/Data Operation')
DB_HOST = os.environ.get('DB_HOST', 'mysql')
DB_PORT = int(os.environ.get('DB_PORT', 3306))
DB_USER = os.environ.get('DB_USER', 'sail')
DB_PASSWORD = os.environ.get('DB_PASSWORD')
DB_NAME = os.environ.get('DB_NAME', 'master_data')

if not DB_PASSWORD:
    print("Error: DB_PASSWORD environment variable is required.")
    exit(1)

def clean_time(time_str):
    if pd.isna(time_str):
         return None
    try:
        # Convert "  12,70" to "12.70"
        cleaned = str(time_str).strip().replace(',', '.')
        if not cleaned or cleaned == '-':
            return None
        return float(cleaned)
    except ValueError:
        return None

def main():
    print("Connecting to MySQL database...")
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = conn.cursor()
    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
        return

    print("Creating labour_codes table if it does not exist...")
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS labour_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            model_prefix VARCHAR(10),
            group_name VARCHAR(255),
            labour_key VARCHAR(50),
            code VARCHAR(255),
            description TEXT,
            time_hours DECIMAL(8,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_model_prefix (model_prefix)
        )
    ''')
    conn.commit()

    print("Clearing old data...")
    conn.start_transaction()
    cursor.execute('DELETE FROM labour_codes')
    conn.commit()

    files = [f for f in os.listdir(DIRECTORY) if f.endswith('.xls') and not f.startswith('.')]
    
    total_inserted = 0
    print(f"Starting to parse {len(files)} files...")

    for filename in files:
        # Extract model prefix from filename (e.g. Data Operation DMS PCMHL167... -> MHL167)
        # Skip the 2-character franchise code (e.g., 'PC')
        match = re.search(r'DMS [A-Z]{2}([A-Z0-9]{6})', filename)
        if not match:
            # Fallback if Franchise is not exactly 2 letters
            parts = filename.split('DMS ')
            if len(parts) > 1:
                # Assuming 'PC' is exactly 2 chars, grab the next 6
                model_prefix = parts[1][2:8].strip()
            else:
                 model_prefix = "UNKNOWN"
        else:
            model_prefix = match.group(1)

        print(f"Processing {filename} (Prefix: {model_prefix})")
        
        filepath = os.path.join(DIRECTORY, filename)
        try:
            xls = pd.ExcelFile(filepath)
            
            insert_data = []
            
            for sheet_name in xls.sheet_names:
                df = pd.read_excel(xls, sheet_name=sheet_name)
                
                # Verify columns exist
                expected_cols = ['Key', 'Code', 'Description', 'Time']
                if not all(col in df.columns for col in expected_cols):
                    continue
                
                for index, row in df.iterrows():
                    labour_key = None
                    if not pd.isna(row['Key']):
                        val = row['Key']
                        if isinstance(val, (int, float)):
                            labour_key = str(int(val)).zfill(6)
                        else:
                            labour_key = str(val).strip().zfill(6)
                    code = str(row['Code']) if not pd.isna(row['Code']) else None
                    desc = str(row['Description']) if not pd.isna(row['Description']) else None
                    time_val = clean_time(row['Time'])
                    
                    if labour_key is None and code is None:
                         continue

                    # Extract the Group number and combine with sheet name
                    group_num = None
                    if 'Group' in df.columns and not pd.isna(row['Group']):
                        try:
                            # Convert 1.0 to 1
                            group_val_float = float(row['Group'])
                            if group_val_float.is_integer():
                                group_num = str(int(group_val_float)).zfill(2)
                            else:
                                group_num = str(group_val_float).zfill(2)
                        except ValueError:
                            group_num = str(row['Group']).strip().zfill(2)

                    sheet_val = str(sheet_name).strip()

                    if group_num is not None and group_num != sheet_val and group_num != "nan":
                        group_val = f"{group_num} - {sheet_val}"
                    else:
                        group_val = sheet_val

                    insert_data.append((
                        model_prefix,
                        group_val,
                        labour_key,
                        code,
                        desc,
                        time_val
                    ))
            
            if insert_data:
                # Insert in chunks to be safe
                query = '''
                    INSERT INTO labour_codes 
                    (model_prefix, group_name, labour_key, code, description, time_hours)
                    VALUES (%s, %s, %s, %s, %s, %s)
                '''
                cursor.executemany(query, insert_data)
                conn.commit()
                total_inserted += len(insert_data)
                print(f"  -> Inserted {len(insert_data)} rows.")

        except Exception as e:
            print(f"Error processing {filename}: {e}")

    print(f"\nDone! Successfully inserted {total_inserted} total rows into the database.")
    conn.commit()
    cursor.close()
    conn.close()

except Exception as e:
    conn.rollback()
    print(f"Error during import, rolling back: {e}")
    raise

if __name__ == '__main__':
    main()
