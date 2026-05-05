# config/db_config.py

import pymysql

DATABASE_HOST     = 'localhost'
DATABASE_PORT     = 3306
DATABASE_NAME     = 'project_python'
DATABASE_USER     = 'root'
DATABASE_PASSWORD = ''

def get_database_connection():
    try:
        conn = pymysql.connect(
            host     = DATABASE_HOST,
            port     = DATABASE_PORT,
            db       = DATABASE_NAME,
            user     = DATABASE_USER,
            password = DATABASE_PASSWORD,
            charset  = 'utf8mb4',
            cursorclass = pymysql.cursors.DictCursor
        )
        return conn
    except pymysql.Error as e:
        print(f'Connection failed: {e}')
        return None