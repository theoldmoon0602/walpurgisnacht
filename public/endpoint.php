<?php
require_once("../line.php");
require_once("../define.php");

// verify request
$logger = new PoorLogger(CONSTANTS::LOGFILE);
$request = new Request(CONSTANTS::ACCESS_TOKEN, CONSTANTS::CHANNEL_SECRET, $logger);
if (! $request->verify()) {
    $logger->info("Signature is mismatched");
    exit();
}
$logger->info("Request: " . $request->getBody());
