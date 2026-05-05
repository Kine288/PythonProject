import os
import sys

from flask import Flask, jsonify

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_ROOT = os.path.abspath(os.path.join(CURRENT_DIR, "..", ".."))

if PROJECT_ROOT not in sys.path:
	sys.path.insert(0, PROJECT_ROOT)
if CURRENT_DIR not in sys.path:
	sys.path.insert(0, CURRENT_DIR)

from api.diem_api import diem_bp

app = Flask(__name__)
app.register_blueprint(diem_bp)


@app.get("/health")
def health_check():
	return jsonify({"status": "ok"})


if __name__ == "__main__":
	host = os.environ.get("FLASK_HOST", "0.0.0.0")
	port = int(os.environ.get("FLASK_PORT", "5000"))
	debug = os.environ.get("FLASK_DEBUG", "1") == "1"
	app.run(host=host, port=port, debug=debug)