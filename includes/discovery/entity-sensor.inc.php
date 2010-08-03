<?php

global $valid_sensor;

echo("ENTITY-SENSOR ");

$oids = array();  
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorType", $oids, "ENTITY-SENSOR-MIB");
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorScale", $oids, "ENTITY-SENSOR-MIB");
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorPrecision", $oids, "ENTITY-SENSOR-MIB");
$oids = snmpwalk_cache_multi_oid($device, "entPhySensorValue", $oids, "ENTITY-SENSOR-MIB");

$entitysensor['voltsDC']   = "voltage";
$entitysensor['voltsAC']   = "voltage";
$entitysensor['amperes']   = "current";
$entitysensor['watt']      = "power";
$entitysensor['hertz']     = "freq";
$entitysensor['percentRH'] = "humidity";
$entitysensor['rpm']       = "fanspeed";
$entitysensor['celsius']   = "temperature";

if(is_array($oids[$device['device_id']]))
{
  foreach($oids[$device['device_id']] as $index => $entry)
  {
    #echo("[" . $entry['entPhySensorType'] . "|" . $entry['entPhySensorValue']. "|" . $index . "]");

    if($entitysensor[$entry['entPhySensorType']] && is_numeric($entry['entPhySensorValue']) && is_numeric($index))
    {
      $entPhysicalIndex = $index;
      $descr = snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-SENSOR-MIB");
      $oid = ".1.3.6.1.2.1.99.1.1.1.4.".$index;
      $current = $entry['entPhySensorValue'];
      #ENTITY-SENSOR-MIB::entPhySensorUnitsDisplay.11 = STRING: "C"

      $descr = snmp_get($device, "entPhysicalName.".$index, "-Oqv", "ENTITY-MIB");
      if(!$descr) { $descr = snmp_get($device, "entPhysicalDescr.".$index, "-Oqv", "ENTITY-MIB"); }

      $valid = TRUE;

      $type = $entitysensor[$entry['entPhySensorType']];

      $descr = str_ireplace("temperature", "", $descr);
      $descr = str_ireplace("sensor", "", $descr);
        
      ### FIXME this stuff is foul

      if($entry['entPhySensorScale'] == "nano")  { $divisor = "1000000000"; $multiplier = "1";  }
      if($entry['entPhySensorScale'] == "micro") { $divisor = "1000000"; $multiplier = "1";  }
      if($entry['entPhySensorScale'] == "milli") { $divisor = "1000"; $multiplier = "1";  }
      if($entry['entPhySensorScale'] == "units") { $divisor = "1"; $multiplier = "1";  }
      if($entry['entPhySensorScale'] == "kilo")  { $divisor = "1"; $multiplier = "1000";  }
      if($entry['entPhySensorScale'] == "mega")  { $divisor = "1"; $multiplier = "1000000";  }
      if($entry['entPhySensorScale'] == "giga")  { $divisor = "1"; $multiplier = "1000000000";  }

      if(is_numeric($entry['entPhySensorPrecision']) && $entry['entPhySensorPrecision'] > "0") { $divisor = $divisor . str_pad('', $entry['entPhySensorPrecision'], "0"); }
      $current = $current * $multiplier / $divisor;

      if($type == "temperature") { if($current > "200"){ $valid = FALSE; } $descr = preg_replace("/[T|t]emperature[|s]/", "", $descr); }

      #echo($descr . "|" . $index . "|" .$current . "|" . $multiplier . "|" . $divisor ."|" . $entry['entPhySensorScale'] . "|" . $entry['entPhySensorPrecision'] . "\n");

      if($valid && mysql_result(mysql_query("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = '".$device['device_id']."' AND `sensor_class` = '".$type."' AND `sensor_type` = 'cisco-entity-sensor' AND `sensor_index` = '".$index."'"),0) == "0") 
      ## Check to make sure we've not already seen this sensor via cisco's entity sensor mib
      {
        discover_sensor($valid_sensor, $type, $device, $oid, $index, 'entity-sensor', $descr, $divisor, $multiplier, NULL, NULL, NULL, NULL, $current);
      }
    }
  }
}

?>
