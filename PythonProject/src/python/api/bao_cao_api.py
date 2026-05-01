from config.db_config import get_database_connection

conn = get_database_connection()
if conn:
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM sinh_vien")
        rows = cursor.fetchall()  # trả về list of dict nhờ DictCursor
    conn.close()