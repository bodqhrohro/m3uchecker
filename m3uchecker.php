<?php
define('BANLIST_NAME', 'banlist.txt');

function dump_html() {
	global $report_fd;

	fwrite($report_fd, ob_get_contents());
	ob_end_clean();
	ob_start();
}

chdir(dirname(__FILE__));

$banlist = [];
if (file_exists(BANLIST_NAME)) {
	$banlist_fd = fopen(BANLIST_NAME, 'r');

	while (($banlist_line = fgets($banlist_fd)) !== FALSE) {
		$banlist[] = $banlist_line;
	}

	fclose($banlist_fd);
}

ob_start();
$timestamp = date('Ymd-His');
$report_name = "report-$timestamp.html";
$report_fd = fopen("reports/$report_name", 'w');
?>
<link rel="stylesheet" href="../m3uchecker.css"/>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<?php
dump_html();

$m3u_fd = fopen('../1119.m3u', 'r');
if (!$m3u_fd) die;

$label = NULL;
$order = 0;

while (($line = fgets($m3u_fd)) !== FALSE):
	if (strpos($line, '#EXTINF') === 0):
		$extinf_tokens = explode(',', $line);
		$label = trim($extinf_tokens[1]);
	else:
		if ($label === NULL) continue;

		$order ++;
		$url = trim($line);

		$stream_count = 0;
		$is_banned = FALSE;

		foreach ($banlist as $banlist_regex) {
			if (preg_match($banlist_regex, $url)) {
				$is_banned = TRUE;
				break;
			}
		}

		if (!$is_banned) {
			$stream_url = $url;

			// detect YouTube links
			if (preg_match('/^https?:\/\/[^\/]*youtu/', $url)) {
				$ytdl_fd = popen("youtube-dl -g \"$url\"", 'r');
				$stream_url = trim(fgets($ytdl_fd)); // may get empty, ffprobe will fail anyway
				pclose($ytdl_fd);
			}

			// get the number of streams
			$ffprobe_fd = popen("timeout 1m ffprobe -hide_banner \"$stream_url\" 2>&1", 'r');
			while (($ffprobe_line = fgets($ffprobe_fd)) !== FALSE) {
				if (preg_match('/^\s*Stream/', $ffprobe_line)) {
					$stream_count ++;
				}
			}
			pclose($ffprobe_fd);
		}

		$status = 'bad';
		if ($is_banned) {
			$status = 'banned';
		} else if ($stream_count >= 2) {
			$status = 'good';
		} else if ($stream_count == 1) {
			$status = 'warn';
		}

		$label = htmlspecialchars($label);
		$url = htmlspecialchars($url);

?>
<div class="stream stream__<?=$status?>">
	<div class="stream-header">
		<span class="stream-order"><?=$order?></span>
		<span class="stream-name"><?=$label?></span>
	</div>
	<span class="stream-url"><?=$url?></span>
</div>
<?php
		dump_html();

		$label = NULL;
	endif;
endwhile;

fclose($m3u_fd);

$symlink_name = "reports/report.html";
unlink($symlink_name);
symlink($report_name, $symlink_name);

ob_end_clean();
