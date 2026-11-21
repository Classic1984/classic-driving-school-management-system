# Desk Station Monitor Agent

This is the piece that actually looks at a camera. CDSMS itself never
connects to your DVR - it only receives a one-word status ("occupied" or
"empty") from this script, which you run on a PC on the same local
network as your DVR/NVR.

**What it does:** every `poll_interval_seconds` (default 20s), for each
configured desk it grabs one frame from that camera's RTSP stream, checks
whether a person is visible using OpenCV's built-in HOG person detector,
and reports the result to CDSMS's Desk Stations page.

**What it does not do:** record, save, or transmit any video or images.
Only the word "occupied" or "empty" ever leaves this machine.

**Tell your staff.** Even though this only reports presence, not footage,
Nigeria's NDPR expects staff to be told cameras are being used and why.
A short notice ("cameras at this desk are monitored to confirm the desk
is staffed during business hours") covers this.

## 1. Set up CDSMS first

1. Log in to CDSMS as a Director and open **Desk Stations** in the sidebar.
2. Click **Add Station**, name it after the desk (e.g. "Front Desk").
3. Copy the token shown on that station's card - you'll paste it into
   `config.json` below.

## 2. Find each camera's RTSP URL

Your DVR/NVR already has an RTSP URL for each camera channel - you need
the URL, not the DVR's own viewing app. Check the DVR's network/RTSP
settings page for the exact path, but the common patterns are:

| Brand                  | Typical RTSP URL pattern                                         |
|-------------------------|-------------------------------------------------------------------|
| Hikvision               | `rtsp://user:pass@<ip>:554/Streaming/Channels/101`                |
| Dahua                    | `rtsp://user:pass@<ip>:554/cam/realmonitor?channel=1&subtype=0`   |
| CP Plus (Dahua OEM)      | same pattern as Dahua                                             |
| Generic ONVIF camera     | use a free tool like ONVIF Device Manager to discover the exact URL |

If you're not sure, your CCTV installer or the DVR's manual/support page
will have the exact format for your model.

## 3. Install and configure

Requires Python 3.9+.

```bash
cd desk-monitor-agent
pip install -r requirements.txt
cp config.example.json config.json
```

Edit `config.json`:

```json
{
  "cdsms_base_url": "https://your-cdsms-domain.example.com",
  "poll_interval_seconds": 20,
  "stations": [
    {
      "name": "Front Desk",
      "rtsp_url": "rtsp://admin:password@192.168.1.10:554/Streaming/Channels/101",
      "token": "the token you copied from the Desk Stations page"
    }
  ]
}
```

Add one entry per desk/camera you want monitored - the script watches
all of them at once, each on its own thread.

## 4. Run it

```bash
python monitor.py
```

You should see a log line every `poll_interval_seconds` per station,
e.g. `"Front Desk": reported occupied`. Check the Desk Stations page in
CDSMS - the card should move out of "Offline" within a cycle or two.

## 5. Keep it running

This needs to run continuously on a PC that's always on and always on
the same network as the DVR. Don't just leave a terminal window open -
set it up to restart automatically:

**Windows:** create a Scheduled Task that runs
`pythonw.exe C:\path\to\monitor.py` at system startup/logon, with
"Run whether user is logged on or not" and "Restart on failure" enabled.

**Linux (systemd):**

```ini
# /etc/systemd/system/desk-monitor.service
[Unit]
Description=Desk Station Monitor Agent
After=network.target

[Service]
WorkingDirectory=/path/to/desk-monitor-agent
ExecStart=/usr/bin/python3 monitor.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable --now desk-monitor
```

## Troubleshooting

- **"could not read a frame from the camera this cycle"** - usually a
  wrong RTSP URL/credentials, or the PC running this script can't reach
  the DVR on the network. Test the same RTSP URL in VLC
  (Media → Open Network Stream) first; if VLC can't play it, the script
  won't either.
- **Station stays "Offline" in CDSMS** - check the token in
  `config.json` matches exactly what's shown on the station's card, and
  that this PC can reach `cdsms_base_url` (try opening it in a browser
  on the same machine).
- **Detection seems wrong** (shows empty when someone's there, or vice
  versa) - the camera angle matters a lot for a person detector; aim it
  so a seated person is reasonably visible (head/shoulders at least),
  not a sharp overhead angle.
