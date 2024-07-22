<?php

ini_set("display_errors", 1);

include_once("params_dest.php");
include_once("utils.php");

$rulesPayloadPath = RULES_PAYLOAD_PATH;

if(!file_exists($rulesPayloadPath))
  die("Invalid payload rules path " . $rulesPayloadPath);

if ($handle = opendir($rulesPayloadPath)){
  $urlRules = OH_API_BASEURL . "/rules";
  while (false !== ($entry = readdir($handle))){
    $tmp = explode("_", $entry);
    if(array_pop($tmp) == "rules.json"){
      echo "Found file: " . $entry . PHP_EOL;
      $payload = file_get_contents($rulesPayloadPath . "/" . $entry);
      $responsePlain = curlPOST($urlRules, $payload, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
      echo "API Response" . PHP_EOL . "\tcode => " . $responsePlain["httpcode"] . PHP_EOL . "\tbody => " . $responsePlain["response"] . PHP_EOL . PHP_EOL;
    }
  }
}