"""Database helpers shared by Python services."""

from contextlib import contextmanager
from typing import Any, Callable

from config.db_config import get_database_connection


def get_connection():
	"""Open a MySQL connection using project configuration."""
	conn = get_database_connection()
	if conn is None:
		raise RuntimeError("Khong the ket noi den CSDL")
	return conn


@contextmanager
def db_cursor(commit=False):
	"""Yield a cursor and optionally commit at the end."""
	conn = get_connection()
	try:
		with conn.cursor() as cursor:
			yield conn, cursor
		if commit:
			conn.commit()
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()


@contextmanager
def db_transaction():
	"""Yield a cursor inside one explicit transaction."""
	conn = get_connection()
	try:
		conn.begin()
		with conn.cursor() as cursor:
			yield conn, cursor
		conn.commit()
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()


def execute_transaction(callback: Callable[[Any], Any]) -> Any:
	"""Execute callback(cursor) in one transaction and return callback result."""
	conn = get_connection()
	try:
		conn.begin()
		with conn.cursor() as cursor:
			result = callback(cursor)
		conn.commit()
		return result
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()
