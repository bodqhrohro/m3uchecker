The path is currently hardcoded, sorry.

The latest report is always available by `reports/report.html` symlink. So your HTTP server needs to have symlinks enabled. Listing of the `reports` folder should be safe too.

The optional `banlist.txt` file is supported, which should contain regexp lines like `/awful-domain\.pig/`. URLs that match the regexps are not checked.

# Requirements

* PHP >= 5.4
* `ffprobe`
* `timeout` from GNU coreutils (or similar)
* `youtube-dl` (only if the playlist may contain YouTube links)
