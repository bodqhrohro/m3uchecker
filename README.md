The path is currently hardcoded, sorry.

Requires PHP >= 5.4 and `ffprobe`.

The latest report is always available by `reports/report.html` symlink. So your HTTP server needs to have symlinks enabled. Listing of the `reports` folder should be safe too.

The optional `banlist.txt` file is supported, which should contain regexp lines like `/awful-domain\.pig/`. URLs that match the regexps are not checked.
