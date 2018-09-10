<?php

$url = "https://poggit.pmmp.io/releases.json?name=DevTools";
$json = file_get_contents($url);
$highestVersion = "0.0.0";
$artifactUrl = "";
if($json !== false){
	$releases = json_decode($json, true);
	foreach($releases as $release){
		if(version_compare($highestVersion, $release["version"], ">=")){
			continue;
		}
		$highestVersion = $release["version"];
		$artifactUrl = $release["artifact_url"];
	}
} else {
	echo "Couldn't get content from $url";
	exit(1);
}

file_put_contents("PocketMine-MP/plugins/DevTools.phar", file_get_contents($artifactUrl));

foreach(scandir("PocketMine-MP") as $f){
	echo "$f\n";
}
foreach(scandir("PocketMine-MP/plugins") as $f){
	echo "$f\n";
}

$server = proc_open(PHP_BINARY . " PocketMine-MP/PocketMine-MP.phar --no-wizard --disable-readline", [
	0 => ['pipe', 'r'],
	1 => ['pipe', 'w'],
	2 => ['pipe', 'w'],
], $pipes);

fwrite($pipes[0], "blocksniper\nmakeplugin BlockSniper\nstop\n\n");
while(!feof($pipes[1])){
	echo fgets($pipes[1]);
}

fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);

echo "\n\nReturn value: " . proc_close($server) . "\n";
if(count(glob('PocketMine-MP/plugin_data/DevTools/BlockSniper*.phar')) === 0){
	echo "The BlockSniper Travis CI build failed.\n";
	exit(1);
}

echo "The BlockSniper Travis CI build succeeded.\n";
exit(0);