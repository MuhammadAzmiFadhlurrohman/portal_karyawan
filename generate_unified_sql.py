import re
import datetime

print("Starting generation of database_unified_msn.sql...")

def parse_sql_values(values_str):
    rows = []
    i = 0
    n = len(values_str)
    
    while i < n:
        while i < n and values_str[i] != '(':
            i += 1
        if i >= n:
            break
        i += 1 # skip '('
        
        row = []
        current_val = []
        in_quotes = False
        quote_char = None
        escape = False
        
        while i < n:
            c = values_str[i]
            
            if escape:
                current_val.append(c)
                escape = False
                i += 1
                continue
                
            if c == '\\':
                escape = True
                i += 1
                continue
                
            if in_quotes:
                if c == quote_char:
                    if i + 1 < n and values_str[i+1] == quote_char:
                        current_val.append(c)
                        i += 2
                        continue
                    else:
                        in_quotes = False
                        quote_char = None
                else:
                    current_val.append(c)
                i += 1
                continue
            
            if c in ("'", '"'):
                in_quotes = True
                quote_char = c
                i += 1
                continue
                
            if c == ',':
                val_str = "".join(current_val).strip()
                if val_str.upper() == 'NULL':
                    row.append(None)
                else:
                    row.append(val_str)
                current_val = []
                i += 1
                continue
                
            if c == ')':
                val_str = "".join(current_val).strip()
                if val_str.upper() == 'NULL':
                    row.append(None)
                elif val_str:
                    row.append(val_str)
                elif len(row) > 0 and len(current_val) == 0:
                    pass
                rows.append(row)
                i += 1
                break
                
            current_val.append(c)
            i += 1
            
    return rows

def extract_table_rows(filename, table_name):
    all_rows = []
    pattern = re.compile(rf'INSERT\s+INTO\s+[`"]?{table_name}[`"]?(?:\s+|\(|$)', re.I)
    with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
        for line in f:
            if pattern.search(line):
                idx = line.find('VALUES')
                if idx == -1:
                    idx = line.find('values')
                if idx != -1:
                    val_part = line[idx + 6:].strip().rstrip(';')
                    rows = parse_sql_values(val_part)
                    all_rows.extend(rows)
    return all_rows

# Build Unified Users
att_users = extract_table_rows('attendance_db.sql', 'users')
gomsn_users = extract_table_rows('gomsn.sql', 'users_unified')

unified_users = []
att_id_map = {}
gomsn_id_map = {}

att_by_code = {}
att_by_phone = {}
att_by_email = {}
att_by_name = {}

for u in att_users:
    aid = int(u[0])
    code = (u[2] or '').strip().upper()
    phone = (u[10] or '').strip().replace(' ', '').replace('-', '')
    email = (u[7] or '').strip().lower()
    name = (u[1] or '').strip().upper()
    
    if code:
        att_by_code[code] = u
    if phone and len(phone) >= 8:
        att_by_phone[phone] = u
    if email and email != 'none' and email != '-':
        att_by_email[email] = u
    if name:
        att_by_name[name] = u

mapped_att_ids = set()
used_employee_codes = set()
used_usernames = set()
used_emails = set()
used_phones = set()

next_id = 1

for gu in gomsn_users:
    gid = int(gu[0])
    g_code = (gu[12] or '').strip().upper()
    g_user = (gu[1] or '').strip().lower().replace(' ', '_').replace('.', '')
    g_name = (gu[3] or gu[1] or '').strip()
    g_email = (gu[13] or '').strip().lower()
    g_phone = (gu[2] or '').strip().replace(' ', '').replace('-', '')
    g_role = (gu[7] or '').strip()
    g_div = (gu[8] or '').strip()
    g_bank = (gu[10] or '').strip()
    g_norek = (gu[9] or '').strip()
    g_atasnama = (gu[11] or '').strip()
    g_foto = (gu[5] or '').strip()
    g_pass = gu[6]
    
    matched_au = None
    if g_code and g_code in att_by_code:
        matched_au = att_by_code[g_code]
    elif g_phone and g_phone in att_by_phone:
        matched_au = att_by_phone[g_phone]
    elif g_email and g_email in att_by_email:
        matched_au = att_by_email[g_email]
    elif g_name.upper() in att_by_name:
        matched_au = att_by_name[g_name.upper()]
    elif 'AZMI' in g_name.upper() or 'AZMI' in g_user.upper():
        matched_au = att_by_code.get('KR92590')
    elif 'AMELIA' in g_name.upper():
        matched_au = att_by_code.get('KR48567')
    elif 'FINANCE' in g_user.upper() or 'KARMELIA' in g_email.upper():
        matched_au = att_by_code.get('KR50001')
    elif 'WAWAN' in g_name.upper() and 'DARMAWAN' not in g_name.upper():
        matched_au = att_by_code.get('KR10699')
    elif 'IWAN' in g_name.upper() or ('RIDWAN' in g_name.upper() and 'MUSTOPA' not in g_name.upper()):
        matched_au = att_by_code.get('KR31363')

    if matched_au and int(matched_au[0]) in att_id_map:
        existing_uid = att_id_map[int(matched_au[0])]
        gomsn_id_map[gid] = existing_uid
        continue

    uid = next_id
    next_id += 1
    gomsn_id_map[gid] = uid
    
    if matched_au:
        aid = int(matched_au[0])
        att_id_map[aid] = uid
        mapped_att_ids.add(aid)
        
        final_code = (matched_au[2] or g_code or f"KR{uid:05d}").strip().upper()
        final_name = (matched_au[1] or g_name).strip()
        final_email = matched_au[7] if (matched_au[7] and matched_au[7] != 'None' and '@' in matched_au[7]) else (g_email if (g_email and '@' in g_email) else f"user{uid}@ptmsn.co.id")
        final_phone = matched_au[10] if (matched_au[10] and matched_au[10] != 'None' and len(matched_au[10]) >= 8) else (g_phone if len(g_phone) >= 8 else f"08100000{uid:04d}")
        final_nik = matched_au[3] if (matched_au[3] and matched_au[3] != 'None') else None
        final_div = matched_au[11] if (matched_au[11] and matched_au[11] != 'None') else (g_div if g_div and g_div != '..' else 'Umum')
        final_join = matched_au[14] if (matched_au[14] and matched_au[14] != 'None') else '2026-07-01'
        final_potongan = matched_au[16] if (matched_au[16] and matched_au[16] != 'None') else 0
        final_foto = g_foto if g_foto else matched_au[8]
        final_pass = g_pass or matched_au[18]
    else:
        final_code = g_code or f"KR{uid:05d}"
        final_name = g_name or g_user
        final_email = g_email if (g_email and '@' in g_email and g_email != '-') else f"{g_user}_{uid}@ptmsn.co.id"
        final_phone = g_phone if len(g_phone) >= 8 else f"08100000{uid:04d}"
        final_nik = None
        final_div = g_div if g_div and g_div != '..' else 'Umum'
        final_join = '2026-07-01'
        final_potongan = 0
        final_foto = g_foto
        final_pass = g_pass

    base_user = g_user if g_user else f"user_{uid}"
    username = base_user
    u_idx = 1
    while username in used_usernames:
        username = f"{base_user}_{u_idx}"
        u_idx += 1
    used_usernames.add(username)
    
    code = final_code
    c_idx = 1
    while code in used_employee_codes:
        code = f"{final_code}_{c_idx}"
        c_idx += 1
    used_employee_codes.add(code)
    
    email = final_email
    e_idx = 1
    while email in used_emails:
        parts = final_email.split('@')
        email = f"{parts[0]}_{e_idx}@{parts[1]}"
        e_idx += 1
    used_emails.add(email)
    
    phone = final_phone
    p_idx = 1
    while phone in used_phones:
        phone = f"{final_phone[:-2]}{p_idx:02d}"
        p_idx += 1
    used_phones.add(phone)

    unified_users.append({
        'id': uid,
        'employee_code': code,
        'username': username,
        'name': final_name,
        'email': email,
        'phone': phone,
        'nik': final_nik,
        'division': final_div,
        'position': 'Staff',
        'supervisor_id': None,
        'join_date': final_join,
        'potongan_alpa': final_potongan,
        'password': final_pass,
        'foto': final_foto,
        'bank': g_bank,
        'no_rekening': g_norek,
        'atas_nama': g_atasnama,
        'role': g_role or (matched_au[20] if matched_au else 'karyawan')
    })

for au in att_users:
    aid = int(au[0])
    if aid in att_id_map:
        continue
        
    uid = next_id
    next_id += 1
    att_id_map[aid] = uid
    
    name = (au[1] or f"User {aid}").strip()
    raw_user = name.lower().replace(' ', '_').replace('.', '')
    code = (au[2] or f"KR{uid:05d}").strip().upper()
    email = (au[7] or '').strip().lower()
    if not email or email == 'none' or '@' not in email:
        email = f"{raw_user}_{uid}@ptmsn.co.id"
    phone = (au[10] or '').strip().replace(' ', '').replace('-', '')
    if not phone or len(phone) < 8 or phone == 'none':
        phone = f"08100000{uid:04d}"
        
    nik = au[3] if (au[3] and au[3] != 'None') else None
    div = au[11] if (au[11] and au[11] != 'None') else 'Umum'
    join = au[14] if (au[14] and au[14] != 'None') else '2026-07-01'
    potongan = au[16] if (au[16] and au[16] != 'None') else 0
    foto = au[8] if au[8] else None
    pwd = au[18]
    role = au[20] if au[20] else 'employee'

    username = raw_user
    u_idx = 1
    while username in used_usernames:
        username = f"{raw_user}_{u_idx}"
        u_idx += 1
    used_usernames.add(username)
    
    c_idx = 1
    while code in used_employee_codes:
        code = f"{code}_{c_idx}"
        c_idx += 1
    used_employee_codes.add(code)
    
    e_idx = 1
    while email in used_emails:
        parts = email.split('@')
        email = f"{parts[0]}_{e_idx}@{parts[1]}"
        e_idx += 1
    used_emails.add(email)
    
    p_idx = 1
    while phone in used_phones:
        phone = f"{phone[:-2]}{p_idx:02d}"
        p_idx += 1
    used_phones.add(phone)

    unified_users.append({
        'id': uid,
        'employee_code': code,
        'username': username,
        'name': name,
        'email': email,
        'phone': phone,
        'nik': nik,
        'division': div,
        'position': 'Staff',
        'supervisor_id': None,
        'join_date': join,
        'potongan_alpa': potongan,
        'password': pwd,
        'foto': foto,
        'bank': None,
        'no_rekening': None,
        'atas_nama': None,
        'role': role
    })

# Handle alias/legacy user IDs
gomsn_id_map[59] = gomsn_id_map[84] # Budi Samsudin in old users table
gomsn_id_map[85] = gomsn_id_map[30] # Legacy atasan fallback to Ipin Aripin

def sql_val(val):
    if val is None:
        return "NULL"
    if isinstance(val, bool):
        return "1" if val else "0"
    if isinstance(val, (int, float)):
        return str(val)
    s = str(val).replace('\\', '\\\\').replace("'", "''")
    return f"'{s}'"

def format_insert(table_name, columns, rows):
    if not rows:
        return ""
    col_str = ", ".join([f"`{c}`" for c in columns])
    out = [f"-- ----------------------------", f"-- Records of {table_name} ({len(rows)} rows)", f"-- ----------------------------"]
    
    chunk_size = 500
    for i in range(0, len(rows), chunk_size):
        chunk = rows[i:i + chunk_size]
        val_lines = []
        for r in chunk:
            row_str = ", ".join([sql_val(v) for v in r])
            val_lines.append(f"({row_str})")
        out.append(f"INSERT INTO `{table_name}` ({col_str}) VALUES\n" + ",\n".join(val_lines) + ";\n")
    return "\n".join(out) + "\n"

# Read Base Schema DDL
with open('database_schema_mysql.sql', 'r', encoding='utf-8') as f:
    schema_sql = f.read()

mig_idx = schema_sql.find('-- Table structure for migrations')
if mig_idx != -1:
    ddl_part = schema_sql[:mig_idx].strip()
    mig_part = schema_sql[mig_idx:].strip()
else:
    ddl_part = schema_sql.strip()
    mig_part = ""

output_lines = []
output_lines.append("/*")
output_lines.append(" Navicat / MySQL Dump")
output_lines.append(" Project       : GOMSN2 - Portal Karyawan PT Media Sarana Nusa")
output_lines.append(" Description   : Unified & Consolidated Database (Attendance + Pengajuan + Lembur + HRIS)")
output_lines.append(f" Generated Date: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
output_lines.append(" Target DBMS   : MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+")
output_lines.append("*/\n")
output_lines.append("SET NAMES utf8mb4;")
output_lines.append("SET FOREIGN_KEY_CHECKS = 0;\n")

# 1. DDL
output_lines.append("-- ========================================================")
output_lines.append("-- 1. DDL: TABLE DEFINITIONS & INDICES")
output_lines.append("-- ========================================================\n")
output_lines.append(ddl_part)
output_lines.append("\n")

# 2. Core Master Data
output_lines.append("-- ========================================================")
output_lines.append("-- 2. CORE MASTER DATA (Roles, Locations, Shifts, Settings)")
output_lines.append("-- ========================================================\n")

roles_cols = ['id', 'name', 'guard_name', 'created_at', 'updated_at']
roles_data = [
    [1, 'super_admin', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [2, 'admin', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [3, 'hrd', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [4, 'finance', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [5, 'atasan_noc', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [6, 'atasan_teknik', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [7, 'atasan_umum', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [8, 'karyawan', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [9, 'ceo', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [10, 'noc', 'web', '2026-11-04 06:18:40', '2026-11-04 06:18:40']
]
output_lines.append(format_insert('roles', roles_cols, roles_data))

offices_cols = ['id', 'code', 'name', 'address', 'lat', 'lng', 'radius_m', 'is_active', 'created_at', 'updated_at']
offices_data = [
    [1, 'BDG-HQ', 'Kantor Pusat Bandung', 'Bandung, Jawa Barat', -6.937122, 107.631989, 50, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [2, 'SRG-OPS', 'Kantor Operasional Soreang', 'Soreang, Kab. Bandung, Jawa Barat', -7.042215, 107.540981, 50, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40']
]
output_lines.append(format_insert('office_locations', offices_cols, offices_data))

shifts_cols = ['id', 'code', 'name', 'start_time', 'end_time', 'color', 'late_tolerance_minutes', 'break_minutes', 'is_day_off', 'is_piket', 'is_active', 'created_at', 'updated_at']
shifts_data = [
    [1, 'P', 'Shift Pagi', '08:15:00', '17:00:00', '#1E40AF', 15, 60, 0, 0, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [2, 'S', 'Shift Siang', '13:00:00', '21:00:00', '#2563EB', 15, 60, 0, 0, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [3, 'SB', 'Shift Subuh', '06:00:00', '15:00:00', '#0284C7', 15, 60, 0, 0, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [4, 'BJ', 'BJS (Visit Soreang)', '08:15:00', '17:00:00', '#0D9488', 15, 60, 0, 0, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [5, 'PK', 'Piket Pagi', '08:15:00', '17:00:00', '#D97706', 15, 60, 0, 1, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [6, 'PA', 'Piket Siang', '13:00:00', '21:00:00', '#EA580C', 15, 60, 0, 1, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40'],
    [7, 'L', 'Libur', None, None, '#64748B', 0, 0, 1, 0, 1, '2026-11-04 06:18:40', '2026-11-04 06:18:40']
]
output_lines.append(format_insert('shifts', shifts_cols, shifts_data))

# 3. Users & Role Assignments
output_lines.append("-- ========================================================")
output_lines.append("-- 3. UNIFIED USERS & ROLE ASSIGNMENTS")
output_lines.append("-- ========================================================\n")

users_cols = [
    'id', 'employee_code', 'username', 'name', 'email', 'phone', 'nik', 
    'division', 'position', 'supervisor_id', 'join_date', 'potongan_alpa', 
    'must_change_password', 'password_changed_at', 'leaderboard_opt_out', 
    'is_active', 'email_verified_at', 'password', 'remember_token', 
    'foto', 'bank', 'no_rekening', 'atas_nama', 'created_at', 'updated_at'
]
users_rows = []
model_has_roles_rows = []

for u in unified_users:
    uid = u['id']
    users_rows.append([
        uid,
        u['employee_code'],
        u['username'],
        u['name'],
        u['email'],
        u['phone'],
        u['nik'],
        u['division'],
        u['position'] or 'Staff',
        u['supervisor_id'],
        u['join_date'] or '2026-07-01',
        u['potongan_alpa'] or 0,
        0,
        None,
        0,
        1,
        '2026-06-01 00:00:00',
        u['password'] or '$2y$10$ulo5BznyV5Lzx9aWytXusOnK7.pujedQ8Ftfmc8dq1ovVrRgqZOMC',
        None,
        u['foto'],
        u['bank'],
        u['no_rekening'],
        u['atas_nama'],
        '2026-06-01 00:00:00',
        '2026-11-10 00:00:00'
    ])
    
    raw_role = (u.get('role') or '').lower()
    div = (u.get('division') or '').lower()
    if uid == 1 or 'admin' in raw_role:
        assigned_role_id = 2
    elif 'hrd' in raw_role:
        assigned_role_id = 3
    elif 'finance' in raw_role:
        assigned_role_id = 4
    elif 'ceo' in raw_role or 'direktur' in div:
        assigned_role_id = 9
    elif 'atasan' in raw_role:
        if 'noc' in div:
            assigned_role_id = 5
        elif 'teknik' in div:
            assigned_role_id = 6
        else:
            assigned_role_id = 7
    elif 'noc' in raw_role or 'noc' in div:
        assigned_role_id = 10
    else:
        assigned_role_id = 8

    model_has_roles_rows.append([assigned_role_id, 'App\\Models\\User', uid])

output_lines.append(format_insert('users', users_cols, users_rows))
output_lines.append(format_insert('model_has_roles', ['role_id', 'model_type', 'model_id'], model_has_roles_rows))

# 4. Master Materials & Movements
output_lines.append("-- ========================================================")
output_lines.append("-- 4. MASTER MATERIALS & MOVEMENTS")
output_lines.append("-- ========================================================")

raw_stok = extract_table_rows('gomsn.sql', 'stok_barang')
materials_cols = ['id', 'kode', 'nama', 'satuan', 'stok_tersedia', 'min_stok', 'is_active', 'created_at', 'updated_at']
materials_rows = []
for s in raw_stok:
    sid = int(s[0])
    materials_rows.append([
        sid,
        f"MAT-{sid:04d}",
        s[1],
        s[3] if len(s) > 3 and s[3] else 'pcs',
        int(s[2]) if s[2] else 0,
        5,
        1,
        '2025-06-01 00:00:00',
        '2026-11-10 00:00:00'
    ])
output_lines.append(format_insert('master_materials', materials_cols, materials_rows))

raw_riwayat = extract_table_rows('gomsn.sql', 'riwayat_stok')
movement_cols = ['id', 'master_material_id', 'tiket_id', 'user_id', 'type', 'quantity', 'stock_before', 'stock_after', 'notes', 'created_at']
movement_rows = []
for r in raw_riwayat:
    mid = int(r[0])
    mat_id = int(r[1])
    gid = int(r[6]) if r[6] else None
    uid = gomsn_id_map.get(gid, 1)
    aksi = r[3]
    m_type = 'IN_RETURN' if aksi == 'tambah' else 'OUT'
    s_before = int(r[4]) if r[4] else 0
    s_after = int(r[5]) if r[5] else 0
    qty = abs(s_after - s_before) if (s_after and s_before) else 1
    movement_rows.append([
        mid,
        mat_id,
        None,
        uid,
        m_type,
        qty,
        s_before,
        s_after,
        f"{aksi} stok {r[2]}",
        r[8] if len(r) > 8 else '2025-06-01 00:00:00'
    ])
output_lines.append(format_insert('material_movements', movement_cols, movement_rows))

# 5. Monthly Schedules
output_lines.append("-- ========================================================")
output_lines.append("-- 5. MONTHLY WORK SCHEDULES")
output_lines.append("-- ========================================================\n")

raw_schedules = extract_table_rows('attendance_db.sql', 'monthly_schedules')
sched_cols = ['id', 'user_id', 'date', 'shift_id', 'office_location_id', 'is_locked', 'notes', 'created_by', 'updated_by', 'created_at', 'updated_at']
sched_rows = []
shift_map = {
    'morning': 1, 'afternoon': 2, 'subuh': 3,
    'bjs': 4, 'picketing': 5, 'picketing_afternoon': 6, 'holiday': 7
}
for s in raw_schedules:
    sid = int(s[0])
    aid = int(s[1])
    uid = att_id_map.get(aid, 1)
    s_date = s[4]
    raw_sh = s[10] if len(s) > 10 and s[10] else 'morning'
    sh_id = shift_map.get(raw_sh.lower(), 1)
    sched_rows.append([
        sid,
        uid,
        s_date,
        sh_id,
        1,
        0,
        s[7] if len(s) > 7 else None,
        1,
        None,
        s[8] if len(s) > 8 and s[8] else f"{s_date} 00:00:00",
        s[9] if len(s) > 9 and s[9] else f"{s_date} 00:00:00"
    ])
output_lines.append(format_insert('monthly_schedules', sched_cols, sched_rows))

# 6. Attendances
output_lines.append("-- ========================================================")
output_lines.append("-- 6. ATTENDANCES (DEDUPLICATED & CONSOLIDATED)")
output_lines.append("-- ========================================================\n")

raw_attendances = extract_table_rows('attendance_db.sql', 'attendances')
att_cols = [
    'id', 'user_id', 'date', 'shift_id', 'office_location_id', 'attendance_type', 
    'status', 'source', 'clock_in_at', 'clock_out_at', 'captured_at', 
    'lat_in', 'lng_in', 'lat_out', 'lng_out', 'late_minutes', 
    'work_duration_minutes', 'proposed_overtime_minutes', 'notes', 
    'photo_in_primary_path', 'photo_in_challenge_path', 'photo_out_path', 
    'created_at', 'updated_at'
]

att_by_pair = {}
for a in raw_attendances:
    aid = int(a[0])
    att_uid = int(a[1])
    uid = att_id_map.get(att_uid, 1)
    cin = a[3]
    cout = a[6]
    created = a[19]
    a_date = (cin or cout or created or '2026-06-01').split(' ')[0]
    
    pair_key = (uid, a_date)
    
    dur_str = a[23] if len(a) > 23 else ''
    dur_mins = 0
    if dur_str:
        if ':' in str(dur_str):
            parts = str(dur_str).split(':')
            dur_mins = int(parts[0]) * 60 + int(parts[1])
        elif str(dur_str).isdigit():
            dur_mins = int(dur_str)

    off_loc = 2 if (len(a) > 18 and a[18] and 'soreang' in str(a[18]).lower()) else 1
    
    record = {
        'id': aid,
        'user_id': uid,
        'date': a_date,
        'shift_id': 1,
        'office_location_id': off_loc,
        'attendance_type': a[13] if len(a) > 13 and a[13] else 'office',
        'status': a[25] if len(a) > 25 and a[25] else 'present',
        'source': 'face',
        'clock_in_at': cin,
        'clock_out_at': cout,
        'captured_at': cin or created,
        'lat_in': a[9] if len(a) > 9 else None,
        'lng_in': a[10] if len(a) > 10 else None,
        'lat_out': a[11] if len(a) > 11 else None,
        'lng_out': a[12] if len(a) > 12 else None,
        'late_minutes': int(a[16]) if (len(a) > 16 and a[16] and str(a[16]).isdigit()) else 0,
        'work_duration_minutes': dur_mins,
        'proposed_overtime_minutes': int(a[24]) if (len(a) > 24 and a[24] and str(a[24]).isdigit()) else 0,
        'notes': a[14] if len(a) > 14 else None,
        'photo_in_primary_path': a[4] if len(a) > 4 else None,
        'photo_in_challenge_path': a[5] if len(a) > 5 else None,
        'photo_out_path': a[7] if len(a) > 7 else None,
        'created_at': created or cin or f"{a_date} 00:00:00",
        'updated_at': a[20] if len(a) > 20 and a[20] else (cout or created or f"{a_date} 00:00:00")
    }

    if pair_key not in att_by_pair:
        att_by_pair[pair_key] = record
    else:
        cur = att_by_pair[pair_key]
        if not cur['clock_out_at'] and record['clock_out_at']:
            cur['clock_out_at'] = record['clock_out_at']
            cur['photo_out_path'] = record['photo_out_path']
            cur['lat_out'] = record['lat_out']
            cur['lng_out'] = record['lng_out']
        if not cur['clock_in_at'] and record['clock_in_at']:
            cur['clock_in_at'] = record['clock_in_at']
            cur['photo_in_primary_path'] = record['photo_in_primary_path']
        cur['late_minutes'] = max(cur['late_minutes'], record['late_minutes'])
        cur['work_duration_minutes'] = max(cur['work_duration_minutes'], record['work_duration_minutes'])
        cur['proposed_overtime_minutes'] = max(cur['proposed_overtime_minutes'], record['proposed_overtime_minutes'])

att_rows = []
for r in att_by_pair.values():
    att_rows.append([
        r['id'], r['user_id'], r['date'], r['shift_id'], r['office_location_id'],
        r['attendance_type'], r['status'], r['source'], r['clock_in_at'], r['clock_out_at'],
        r['captured_at'], r['lat_in'], r['lng_in'], r['lat_out'], r['lng_out'],
        r['late_minutes'], r['work_duration_minutes'], r['proposed_overtime_minutes'],
        r['notes'], r['photo_in_primary_path'], r['photo_in_challenge_path'],
        r['photo_out_path'], r['created_at'], r['updated_at']
    ])
output_lines.append(format_insert('attendances', att_cols, att_rows))

# 7. Leaves & Leave Balances
output_lines.append("-- ========================================================")
output_lines.append("-- 7. LEAVES & LEAVE BALANCES")
output_lines.append("-- ========================================================")

raw_leaves = extract_table_rows('attendance_db.sql', 'leaves')
leave_cols = ['id', 'user_id', 'leave_type', 'start_date', 'end_date', 'working_days', 'reason', 'attachment_path', 'status', 'is_alpa_conversion', 'supervisor_approved_at', 'hrd_approved_at', 'created_at', 'updated_at']
leave_rows = []
for lv in raw_leaves:
    lid = int(lv[0])
    aid = int(lv[1])
    uid = att_id_map.get(aid, 1)
    status = lv[9] if len(lv) > 9 and lv[9] else 'approved'
    st_date = lv[2]
    end_date = lv[3]
    app_at = lv[11] if len(lv) > 11 else None
    leave_rows.append([
        lid,
        uid,
        'annual',
        st_date,
        end_date,
        int(lv[4]) if len(lv) > 4 and lv[4] else 1,
        lv[5] if len(lv) > 5 and lv[5] else 'Cuti',
        lv[7] if len(lv) > 7 else None,
        status,
        0,
        app_at,
        app_at,
        lv[10] if len(lv) > 10 and lv[10] else f"{st_date} 00:00:00",
        app_at or f"{st_date} 00:00:00"
    ])
output_lines.append(format_insert('leaves', leave_cols, leave_rows))

raw_balances = extract_table_rows('attendance_db.sql', 'leave_balances')
lb_cols = ['id', 'user_id', 'year', 'annual_quota', 'carried_over', 'used_days', 'alpa_deducted_days', 'remaining_days', 'is_locked_override', 'created_at', 'updated_at']
lb_rows = []
seen_lb = set()
for b in raw_balances:
    bid = int(b[0])
    aid = int(b[1])
    uid = att_id_map.get(aid, 1)
    yr_val = str(b[9]) if (len(b) > 9 and b[9]) else (str(b[8]) if len(b) > 8 and b[8] else '2026')
    try:
        yr = int(yr_val[:4])
    except:
        yr = 2026
    if (uid, yr) in seen_lb:
        continue
    seen_lb.add((uid, yr))
    q = int(b[2]) if len(b) > 2 and b[2] else 12
    rem = int(b[3]) if len(b) > 3 and b[3] else 12
    used = int(b[4]) if len(b) > 4 and b[4] else (q - rem)
    c_at = b[10] if (len(b) > 10 and b[10] and len(str(b[10])) > 7) else f"{yr}-01-01 00:00:00"
    u_at = b[11] if (len(b) > 11 and b[11] and len(str(b[11])) > 7) else c_at
    lb_rows.append([
        bid,
        uid,
        yr,
        q,
        0,
        used,
        0,
        rem,
        0,
        c_at,
        u_at
    ])
output_lines.append(format_insert('leave_balances', lb_cols, lb_rows))

# 8. Pengajuan & Detail
output_lines.append("-- ========================================================")
output_lines.append("-- 8. PENGAJUAN (DANA & BARANG) & DETAIL")
output_lines.append("-- ========================================================")

raw_pengajuan = extract_table_rows('gomsn.sql', 'pengajuan')
p_cols = [
    'id', 'nomor_pengajuan', 'jenis', 'user_id', 'atasan_id', 'judul', 'keperluan', 
    'nominal_total', 'status', 'requires_executive_approval', 'current_approver_stage', 
    'bukti_transfer_path', 'transfer_at', 'nominal_realisasi', 'selisih_realisasi', 
    'bukti_realisasi_path', 'realisasi_at', 'realisasi_notes', 'created_at', 'updated_at'
]
p_rows = []

atasan_name_map = {}
for u in unified_users:
    uname = u['name'].upper()
    atasan_name_map[uname] = u['id']
    atasan_name_map[u['username'].upper()] = u['id']

for p in raw_pengajuan:
    pid = int(p[0])
    gid = int(p[1]) if p[1] else None
    uid = gomsn_id_map.get(gid, 1)
    
    st_atasan = (p[2] or '').lower()
    st_finance = (p[3] or '').lower()
    created = p[4] or '2025-06-01 00:00:00'
    raw_jenis = (p[5] or '').lower()
    jenis = 'DANA' if 'dana' in raw_jenis else 'BARANG'
    
    if st_finance == 'disetujui' and st_atasan == 'disetujui':
        status = 'selesai'
    elif st_atasan == 'ditolak' or st_finance == 'ditolak':
        status = 'ditolak'
    elif st_atasan == 'disetujui' and st_finance == 'pending':
        status = 'menunggu_finance'
    elif st_atasan == 'pending':
        status = 'menunggu_atasan'
    else:
        status = 'selesai'

    bukti_trf = p[8] if len(p) > 8 and p[8] else None
    bukti_beli = p[9] if len(p) > 9 and p[9] else None
    atasan_str = (p[10] or '').upper() if len(p) > 10 else ''
    atasan_id = atasan_name_map.get(atasan_str, 2)

    detail_str = p[14] if len(p) > 14 and p[14] else ''
    nominal_raw = p[15] if len(p) > 15 and p[15] else '0'
    try:
        nominal = float(str(nominal_raw).replace('.', '').replace(',', '.'))
    except:
        nominal = 0.0

    ket = p[17] if len(p) > 17 and p[17] else detail_str or 'Pengajuan Operasional'
    judul = detail_str[:150] if detail_str else ket[:150]
    if not judul:
        judul = f"Pengajuan {jenis} #{pid}"

    dt_str = created.split(' ')[0].replace('-', '')
    no_pengajuan = f"PJ-{dt_str}-{pid:04d}"

    p_rows.append([
        pid,
        no_pengajuan,
        jenis,
        uid,
        atasan_id,
        judul,
        ket,
        nominal,
        status,
        0,
        None,
        bukti_trf,
        created if status == 'selesai' else None,
        nominal if status == 'selesai' else None,
        0.0 if status == 'selesai' else None,
        bukti_beli,
        created if bukti_beli else None,
        None,
        created,
        created
    ])
output_lines.append(format_insert('pengajuan', p_cols, p_rows))

raw_p_detail = extract_table_rows('gomsn.sql', 'pengajuan_dana_detail')
pd_cols = ['id', 'pengajuan_id', 'deskripsi', 'jumlah', 'harga_satuan', 'subtotal', 'master_material_id', 'created_at', 'updated_at']
pd_rows = []
for pd in raw_p_detail:
    pdid = int(pd[0])
    pid = int(pd[1])
    desc = pd[2] or 'Item Pengajuan'
    amt = float(pd[3]) if pd[3] else 0.0
    pd_rows.append([
        pdid,
        pid,
        desc,
        1,
        amt,
        amt,
        None,
        '2025-06-01 00:00:00',
        '2026-11-10 00:00:00'
    ])
output_lines.append(format_insert('pengajuan_dana_detail', pd_cols, pd_rows))

# 9. Lembur (Overtime)
output_lines.append("-- ========================================================")
output_lines.append("-- 9. LEMBUR (OVERTIME RECORDS)")
output_lines.append("-- ========================================================")

raw_lembur = extract_table_rows('gomsn.sql', 'lembur')
l_cols = [
    'id', 'nomor_lembur', 'user_id', 'atasan_id', 'approver_id', 'tanggal', 
    'jam_mulai', 'jam_selesai', 'durasi_menit', 'alasan', 'status', 
    'approved_at', 'rejection_reason', 'is_reconciled', 'reconciliation_flag', 
    'paid_at', 'paid_by', 'payment_reference', 'jenis_lembur', 'created_at', 'updated_at'
]
l_rows = []

for l in raw_lembur:
    lid = int(l[0])
    gid = int(l[1]) if l[1] else None
    uid = gomsn_id_map.get(gid, 1)
    
    g_atasan = int(l[2]) if l[2] else None
    atasan_id = gomsn_id_map.get(g_atasan, 2)
    
    tgl = l[3] or '2025-06-01'
    j_mulai = f"{tgl} {l[4]}" if len(l) > 4 and l[4] else f"{tgl} 08:00:00"
    j_selesai = f"{tgl} {l[5]}" if len(l) > 5 and l[5] else f"{tgl} 17:00:00"
    
    dur_raw = l[6] if len(l) > 6 and l[6] else None
    try:
        dur_mins = int(float(dur_raw) * 60) if dur_raw else 120
    except:
        dur_mins = 120

    alasan = l[7] if len(l) > 7 and l[7] else 'Lembur pekerjaan'
    raw_st = (l[8] or '').lower()
    is_paid = (l[12] == '1' or (len(l) > 11 and l[11] == 'sudah'))
    
    if is_paid:
        status = 'paid'
    elif raw_st == 'disetujui':
        status = 'approved_atasan'
    elif raw_st == 'ditolak':
        status = 'rejected'
    else:
        status = 'pending'

    created = l[9] if len(l) > 9 and l[9] else f"{tgl} 08:00:00"
    app_at = created if (status in ('approved_atasan', 'paid')) else None
    paid_at = created if is_paid else None
    paid_by = 4 if is_paid else None
    jenis_l = l[14] if len(l) > 14 and l[14] and l[14] != 'Pilih Jenis' else 'Hari Kerja'
    
    dt_str = tgl.replace('-', '')
    no_lembur = f"OT-{dt_str}-{lid:04d}"

    l_rows.append([
        lid,
        no_lembur,
        uid,
        atasan_id,
        atasan_id if app_at else None,
        tgl,
        j_mulai,
        j_selesai,
        dur_mins,
        alasan,
        status,
        app_at,
        None,
        1 if status == 'paid' else 0,
        'NORMAL',
        paid_at,
        paid_by,
        f"PAY-OT-{lid:04d}" if is_paid else None,
        jenis_l,
        created,
        created
    ])
output_lines.append(format_insert('lembur', l_cols, l_rows))

# 10. Tasks
output_lines.append("-- ========================================================")
output_lines.append("-- 10. TASKS & OPERATIONAL ASSIGNMENTS")
output_lines.append("-- ========================================================")

raw_tasks = extract_table_rows('gomsn.sql', 'tasks')
task_cols = ['id', 'title', 'description', 'assigner_id', 'assignee_id', 'status', 'priority', 'due_date', 'completed_at', 'tiket_id', 'created_at', 'updated_at']
task_rows = []
for t in raw_tasks:
    tid = int(t[0])
    title = t[1] or f"Task #{tid}"
    desc = t[2] or title
    gid_karyawan = int(t[4]) if len(t) > 4 and t[4] else None
    gid_atasan = int(t[5]) if len(t) > 5 and t[5] else None
    assignee = gomsn_id_map.get(gid_karyawan, 1)
    assigner = gomsn_id_map.get(gid_atasan, 2)
    st = t[6] if len(t) > 6 and t[6] else 'Completed'
    created = t[9] if len(t) > 9 and t[9] else '2026-01-01 00:00:00'
    task_rows.append([
        tid,
        title,
        desc,
        assigner,
        assignee,
        st,
        'medium',
        created,
        created if st == 'Completed' else None,
        None,
        created,
        created
    ])
output_lines.append(format_insert('tasks', task_cols, task_rows))

# 11. Migrations
output_lines.append("-- ========================================================")
output_lines.append("-- 11. LARAVEL MIGRATIONS HISTORY")
output_lines.append("-- ========================================================\n")
output_lines.append(mig_part)
output_lines.append("\nSET FOREIGN_KEY_CHECKS = 1;\n")

final_sql = "\n".join(output_lines)
with open('database_unified_msn.sql', 'w', encoding='utf-8') as f:
    f.write(final_sql)

print(f"DONE! Generated database_unified_msn.sql successfully! Total size: {len(final_sql)} characters.")
