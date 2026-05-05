import os
import sys
from pathlib import Path

from flask import Flask, jsonify

# Ensure project root and current dir are available for imports.
CURRENT_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = CURRENT_DIR.parents[1]

if str(PROJECT_ROOT) not in sys.path:
	sys.path.append(str(PROJECT_ROOT))
if str(CURRENT_DIR) not in sys.path:
	sys.path.append(str(CURRENT_DIR))

from api.diem_api import diem_bp  # noqa: E402
from api.gpa_api import gpa_bp  # noqa: E402
from api.sinh_vien_api import sinh_vien_bp  # noqa: E402


def create_app() -> Flask:
	app = Flask(__name__)

	app.register_blueprint(diem_bp)
	app.register_blueprint(gpa_bp)
	app.register_blueprint(sinh_vien_bp)

	@app.get("/health")
	def health_check():
		return jsonify({"status": "ok"})

	return app


app = create_app()


if __name__ == "__main__":
	host = os.environ.get("FLASK_HOST", "0.0.0.0")
	port = int(os.environ.get("FLASK_PORT", "5000"))
	debug = os.environ.get("FLASK_DEBUG", "1") == "1"
	app.run(host=host, port=port, debug=debug)
