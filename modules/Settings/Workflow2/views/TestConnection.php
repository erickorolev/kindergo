<?php
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;

    curl_setopt($ch, 	CURLOPT_VERBOSE, 1);

    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);

    if(!empty($options['debug'])) {
        var_dump('URL: '.$url);
        echo 'Response:'.PHP_EOL;
        var_dump($data);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        unlink($verbose);
    }

	curl_close($ch);
	return $data;
}

var_dump(get_data('https://www.google.de'));
var_dump(get_data('https://repository.stefanwarnat.de'));
var_dump(get_data('https://repository.redoo-networks.de'));

