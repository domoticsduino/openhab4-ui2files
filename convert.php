<?php

ini_set("display_errors", 1);

include_once("params.php");
include_once("utils.php");

$paths = [
  "root" => OUTPUT_PATH,
  "things" => OUTPUT_PATH . "things/",
  "items" => OUTPUT_PATH . "items/",
  "services" => OUTPUT_PATH . "services/",
  "rules" => OUTPUT_PATH . "rules/"
];

foreach($paths as $p)
  if(!file_exists($p))
    mkdir($p);

$addons = [];
$addonsType = [];
$addonsConfig = [];
$things = [];
$bridges = [];
$items = [];
$groups = [];
$locations = [];
$equipments = [];
$points = [];
$links = [];
$bridgesList = [];
$rules = [];

$urlAddons = OH_API_BASEURL . "/addons";
$responseAddonPlain = curlGET($urlAddons, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
$responseAddon = empty($responseAddonPlain["response"]) ? null : json_decode($responseAddonPlain["response"]);
foreach($responseAddon as $a)
  if(!empty($a->installed)){
    if(!array_key_exists($a->type, $addonsType))
      $addonsType[$a->type] = [];
    array_push($addonsType[$a->type], $a->id);
    $urlAddonsConfig = OH_API_BASEURL . "/addons/" . $a->uid . "/config";
    $responseAddonConfigPlain = curlGET($urlAddonsConfig, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
    if(!empty($responseAddonConfigPlain["response"]) && $responseAddonConfigPlain["response"] != "{}")
      array_push($addonsConfig, $a->uid . PHP_EOL . $responseAddonConfigPlain["response"]);
  }
if(!empty($addonsType))
  foreach($addonsType as $t => $a)
    array_push($addons, $t . " = " . implode(", ", $a));

$urlThings = OH_API_BASEURL . "/things";
$responseThingPlain = curlGET($urlThings, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
$responseThing = empty($responseThingPlain["response"]) ? null : json_decode($responseThingPlain["response"]);
foreach($responseThing as $t)
  if(!empty($t->bridgeUID) && !in_array($t->bridgeUID, $bridgesList))
    array_push($bridgesList, $t->bridgeUID);

foreach($responseThing as $t){
  $isBridge = in_array($t->UID, $bridgesList);
  $str = ($isBridge ? "Bridge" : "Thing") . " " .  $t->UID;
  if(!empty($t->label))
    $str .= " \"" . $t->label . "\"";
  if(!empty($t->bridgeUID))
    $str .= " (" . $t->bridgeUID . ")";
  if(!empty($t->location))
    $str .= " @ \"" . $t->location . "\"";
  $conf = [];
  if(!empty($t->configuration))
    foreach((array)$t->configuration as $k => $c)
      array_push($conf, $k . "=\"" . (is_array($c) ? "[" . str_replace("\"", "\\\"", implode(",", $c)) . "]" : str_replace("\"", "\\\"", $c)) . "\"");
  if(!empty($conf))
    $str .= " [" . implode(", ", $conf) . "]";
  if(in_array($t->thingTypeUID, OH_THINGS_WITH_MAN_CHANNELS) && !empty($t->channels)){
    $channels = [];
    foreach($t->channels as $ch){
      $tmp = explode(":", $ch->channelTypeUID);
      $str2 = "Type " . array_pop($tmp) . " : " . $ch->id;
      if(!empty($ch->label))
        $str2 .= " \"" . $ch->label . "\"";
      $conf = [];
      if(!empty($ch->configuration))
        foreach((array)$ch->configuration as $k => $v)
          array_push($conf, $k . "=\"" . $v . "\"");
      if(!empty($conf))
        $str2 .= " [" . implode(", ", $conf) . "]";
      array_push($channels, $str2);
    }
    if(!empty($channels))
      $str .= "{" . PHP_EOL . "\tChannels:" . PHP_EOL . "\t\t" . implode(PHP_EOL . "\t\t", $channels) . PHP_EOL . "}";
  }
  if($isBridge)
    array_push($bridges, $str);
  else
    array_push($things, $str);
}

$urlLinks = OH_API_BASEURL . "/links";
$responseLinkPlain = curlGET($urlLinks, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
$responseLink = empty($responseLinkPlain["response"]) ? null : json_decode($responseLinkPlain["response"]);
foreach($responseLink as $l){
  if(!array_key_exists($l->itemName, $links))
    $links[$l->itemName] = $l;
  else
    echo "item " . $l->itemName . " ha già un link" . PHP_EOL;
}

$urlItems = OH_API_BASEURL . "/items";
$responsePlain = curlGET($urlItems, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
$response = empty($responsePlain["response"]) ? null : json_decode($responsePlain["response"]);
if(empty($response) || !empty($response->error->message))
  die("ERROR => " . (empty($response) ? "SYSTEM ERROR" : $response->error->message));
foreach($response as $obj){
  $isLocation = false;
  $isEquipment = false;
  $isPoint = false;
  $isGroup = $obj->type == "Group";
  $output = $obj->type . " " . $obj->name;
  if(!empty($obj->label))
    $output .= " \"" . $obj->label;
  $output .= "\" ";
  if(!empty($obj->category))
    $output .= "<" . strtolower($obj->category) . "> ";
  if(!empty($obj->groupNames))
    $output .= "(" . implode(", ", $obj->groupNames) . ") ";
  if(!empty($obj->tags))
    $output .= "[\"" . implode("\", \"", $obj->tags) . "\"] ";
  $bindings = [];
  if(!empty($obj->metadata)){
    foreach((array)$obj->metadata as $k => $m){
      if($k == "semantics"){
        if($m->value == "Location" || strpos($m->value, "Location_") === 0)
          $isLocation = true;
        else if($m->value == "Equipment" || strpos($m->value, "Equipment_") === 0)
          $isEquipment = true;
        else if($m->value == "Point" || strpos($m->value, "Point_") === 0)
          $isPoint = true;
      }
      $str = $k . " = \"" . $m->value . "\"";
      if(!empty($m->config)){
        $arr2 = [];
        foreach((array)$m->config as $k2 => $c)
          array_push($arr2, $k2 . "=\"" . (is_array($c) ? $c[0] : $c) . "\"");
        $str .= " [" . implode(", ", $arr2) . "]";
      }
      array_push($bindings, $str);
    }
  }
  if(array_key_exists($obj->name, $links) && !empty($links[$obj->name]->channelUID))
    array_push($bindings, "channel=\"" . $links[$obj->name]->channelUID . "\"");
  if(!empty($bindings))
    $output .= " { " . implode(", ", $bindings) . " } ";
  if($isLocation)
    array_push($locations, $output);
  else if($isEquipment)
    array_push($equipments, $output);
  else if($isPoint)
    array_push($points, $output);
  else if($isGroup)
    array_push($groups, $output);
  else
    array_push($items, $output);
}

$urlRules = OH_API_BASEURL . "/rules";
$responsePlain = curlGET($urlRules, OH_CLOUD_USERNAME, OH_CLOUD_PASSWORD, ["X-OPENHAB-TOKEN: " . OH_API_TOKEN]);
$response = empty($responsePlain["response"]) ? null : json_decode($responsePlain["response"]);
if(empty($response) || !empty($response->error->message))
  die("ERROR => " . (empty($response) ? "SYSTEM ERROR" : $response->error->message));
foreach($response as $obj)
  file_put_contents($paths["rules"] . $obj->uid . "_rules.json", json_encode($obj));

file_put_contents($paths["root"] . "XX_addonsconfig.txt", implode(PHP_EOL, $addonsConfig));
file_put_contents($paths["services"] . "addons.cfg.append", implode(PHP_EOL, $addons));
file_put_contents($paths["things"] . "00_bridges.things", implode(PHP_EOL, $bridges));
file_put_contents($paths["things"] . "01_things.things", implode(PHP_EOL, $things));
file_put_contents($paths["items"] . "00_locations.items", implode(PHP_EOL, $locations));
file_put_contents($paths["items"] . "01_equipments.items", implode(PHP_EOL, $equipments));
file_put_contents($paths["items"] . "02_groups.items", implode(PHP_EOL, $groups));
file_put_contents($paths["items"] . "03_points.items", implode(PHP_EOL, $points));
file_put_contents($paths["items"] . "04_items.items", implode(PHP_EOL, $items));
