<?php
function dump_html() {
	global $report_fd;

	fwrite($report_fd, ob_get_contents());
	ob_end_clean();
	ob_start();
}

chdir(dirname(__FILE__));

ob_start();
$timestamp = date('Ymd-His');
$report_name = "report-$timestamp.html";
$report_fd = fopen("reports/$report_name", 'w');
?>
<link rel="stylesheet" href="../m3uchecker.css"/>
<meta charset="utf-8"/>
<?php
dump_html();

$m3u_fd = fopen('../1119.m3u', 'r');
if (!$m3u_fd) die;

$label = NULL;

while (($line = fgets($m3u_fd)) !== FALSE):
	if (strpos($line, '#EXTINF') === 0):
		$extinf_tokens = explode(',', $line);
		$label = trim($extinf_tokens[1]);
	else:
		if ($label === NULL) continue;

		$url = trim($line);

		$stream_count = 0;

		$ffprobe_fd = popen("ffprobe -hide_banner \"$url\" 2>&1", 'r');
		while (($ffprobe_line = fgets($ffprobe_fd)) !== FALSE) {
			if (preg_match('/^\s*Stream/', $ffprobe_line)) {
				$stream_count ++;
			}
		}
		pclose($ffprobe_fd);

		$status = 'bad';
		if ($stream_count >= 2) {
			$status = 'good';
		} else if ($stream_count == 1) {
			$status = 'warn';
		}

		$label = htmlspecialchars($label);
		$url = htmlspecialchars($url);

?>
<div class="stream stream__<?=$status?>">
	<span class="stream-name"><?=$label?></span>
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
