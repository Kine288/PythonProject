from pathlib import Path
import sys

from flask import Flask, jsonify


# Ensure project root is available for imports like config.* and src.*
PROJECT_ROOT = Path(__file__).resolve().parents[2]
if str(PROJECT_ROOT) not in sys.path:
	sys.path.append(str(PROJECT_ROOT))

from src.python.api.sinh_vien_api import sinh_vien_bp  # noqa: E402


def create_app() -> Flask:
	app = Flask(__name__)

	app.register_blueprint(sinh_vien_bp)

	@app.get("/health")
	def health_check():
		return jsonify({"success": True, "message": "Python API running"})

	return app


app = create_app()


if __name__ == "__main__":
	app.run(host="127.0.0.1", port=5001, debug=True)