#!/usr/bin/env python3
"""
Desk Station Monitor Agent
===========================

Runs on a PC on the same local network as your DVR/NVR. For each
configured station it periodically grabs a frame from that camera's RTSP
stream, checks whether a person is in frame, and reports "occupied" or
"empty" to CDSMS's Desk Stations feature.

No video is recorded, saved, or sent anywhere - only the word "occupied"
or "empty" ever leaves this machine. See README.md before running this
on a real camera: staff must be told cameras are used this way.

Setup:
    pip install -r requirements.txt
    cp config.example.json config.json
    # edit config.json: cdsms_base_url, and each station's rtsp_url + token
    python monitor.py
"""

import json
import logging
import sys
import threading
import time
from pathlib import Path

import cv2
import requests

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(threadName)s] %(levelname)s %(message)s",
)
log = logging.getLogger("desk-monitor")

CONFIG_PATH = Path(__file__).parent / "config.json"

# How much to shrink each frame before running detection - the HOG person
# detector is slow on a full-resolution frame, and a desk-presence check
# doesn't need full resolution anyway.
DETECTION_WIDTH = 640

# Network/read timeouts are intentionally short: a single failed check
# just means "try again next cycle", not something worth blocking on.
PING_TIMEOUT_SECONDS = 10


def detect_person(frame, hog: cv2.HOGDescriptor) -> bool:
    height, width = frame.shape[:2]
    if width > DETECTION_WIDTH:
        scale = DETECTION_WIDTH / width
        frame = cv2.resize(frame, (DETECTION_WIDTH, int(height * scale)))

    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    rectangles, weights = hog.detectMultiScale(gray, winStride=(8, 8))

    return len(rectangles) > 0


def grab_frame(rtsp_url: str):
    """
    Opens the stream fresh, discards the first couple of frames (RTSP
    streams often serve a stale buffered frame right after connecting),
    and returns one usable frame - or None if the camera couldn't be
    reached this cycle.
    """
    capture = cv2.VideoCapture(rtsp_url)
    try:
        if not capture.isOpened():
            return None

        frame = None
        for _ in range(3):
            ok, frame = capture.read()
            if not ok:
                return None

        return frame
    finally:
        capture.release()


def report_status(cdsms_base_url: str, token: str, status: str) -> None:
    url = f"{cdsms_base_url.rstrip('/')}/public/desk-stations/{token}/ping"

    try:
        response = requests.post(url, json={"status": status}, timeout=PING_TIMEOUT_SECONDS)
        if response.status_code != 200:
            log.warning("CDSMS rejected the ping (HTTP %s): %s", response.status_code, response.text[:200])
    except requests.RequestException as exc:
        log.warning("Could not reach CDSMS: %s", exc)


def watch_station(station: dict, cdsms_base_url: str, poll_interval_seconds: int) -> None:
    name = station["name"]
    rtsp_url = station["rtsp_url"]
    token = station["token"]

    # One HOG detector per thread - it's not thread-safe to share.
    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())

    log.info('Watching "%s"', name)

    while True:
        frame = grab_frame(rtsp_url)

        if frame is None:
            log.warning('"%s": could not read a frame from the camera this cycle', name)
        else:
            occupied = detect_person(frame, hog)
            status = "occupied" if occupied else "empty"
            report_status(cdsms_base_url, token, status)
            log.info('"%s": reported %s', name, status)

        time.sleep(poll_interval_seconds)


def main() -> None:
    if not CONFIG_PATH.exists():
        log.error("config.json not found next to this script. Copy config.example.json to config.json and fill it in first.")
        sys.exit(1)

    config = json.loads(CONFIG_PATH.read_text())
    cdsms_base_url = config["cdsms_base_url"]
    poll_interval_seconds = config.get("poll_interval_seconds", 20)
    stations = config["stations"]

    if not stations:
        log.error("No stations configured in config.json.")
        sys.exit(1)

    threads = [
        threading.Thread(
            target=watch_station,
            args=(station, cdsms_base_url, poll_interval_seconds),
            name=station["name"],
            daemon=True,
        )
        for station in stations
    ]

    for thread in threads:
        thread.start()

    # The watcher threads run forever; this just keeps the main thread
    # alive so Ctrl+C/SIGTERM can stop the whole process.
    for thread in threads:
        thread.join()


if __name__ == "__main__":
    main()
