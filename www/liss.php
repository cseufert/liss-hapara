<?php

/**
 * Class lissServer
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */

class lissServer {
  protected static $user;

  static function hello() {
    return array("SIS"=>"Hapara-Liss Connector", "LissVersion"=>100);
  }
  static function getStudents($auth, $asAtDate) {
    throw new lissError("getStudents is not supported",0x10);
  }
  static function publishStudents($auth, $asAtDate, $students) {
    throw new lissError("publishStudents is not supported",0x10);
  }
  static function getTeachers($auth, $asAtDate) {
    throw new lissError("getTeachers is not supported",0x10);
  }
  static function publishTeachers($auth, $asAtDate, $data) {
    throw new lissError("publishTeachers is not supported",0x10);
  }
  static function getRooms($auth) {
    throw new lissError("getRooms is not supported",0x10);
  }
  static function publishRooms($auth, $asAtDate, $data) {
    throw new lissError("publishRooms is not supported",0x10);
  }

  static function publishClassMemberships($auth, $membership, $asAtDate) {
    self::_doAuth($auth);
    file_put_contents("../data/classmember.".self::$user.".json",json_encode($membership, JSON_PRETTY_PRINT));
    return "";
  }

  static function getClassMembership() {
    throw new lissError("getClassMembership is not supported",0x10);
  }

  static function publishClasses($auth, $academicYear , $classes) {
    self::_doAuth($auth);
    file_put_contents("../data/classlist.".self::$user.".json",json_encode($classes, JSON_PRETTY_PRINT));
    return "";
  }

  static function getClasses($auth, $academicYear) {
    throw new lissError("getClasses is not supported",0x10);
  }
  static function getTimetableStructures($auth, $asAtDate) {
    throw new lissError("getTimetableStructures is not supported",0x10);
  }

  static function publishTimetable($auth, $timetable, $academicYear, $timetableId, $termId, $startDate, $endDate, $createClassesFlag) {
    self::_doAuth($auth);
    file_put_contents("../data/timetable.".self::$user.".json",json_encode($timetable, JSON_PRETTY_PRINT));
    return "";
  }

  static function getTimetable($auth, $academicYearId, $startDate, $endDate) {
    throw new lissError("getTimetable is not supported",0x10);
  }
  static function publishDailyData($auth, $startDate, $endDate, $timetable) {
    self::_doAuth($auth);
    return "";
  }
  static function publishBellTimes($auth, $TtStructure, $periods) {
    throw new lissError("publishBellTimes is not supported",0x10);
  }
  static function getBellTimes($auth, $TtStructure) {
    throw new lissError("getBellTimes is not supported",0x10);
  }
  static function publishCalendar($auth, $d) {
    return "";
    throw new lissError("publishCalendar is not supported",0x10);
  }
  static function getCalendar($auth, $date1, $date2) {
    throw new lissError("getCalendar is not supported",0x10);
  }
  static function changeClassMembership($auth, $studentId, $date, $outOfClasses, $intoClasses) {
    throw new lissError("Change Class Membership is not supported",0x10);
  }
  static function _doAuth($auth) {
    self::$user = $auth["UserName"];
    if(FALSE === strpos(self::$user,".edu.") || !preg_match("/^[a-zA-Z\\.-]+$/",self::$user))
      throw new lissError("Unable to Authenticate with current UserName and Password");
    $conf = array("user"=>$auth["UserName"], "key"=>$auth["Password"]);
    file_put_contents("../data/conf.".self::$user.".json", json_encode($conf, JSON_PRETTY_PRINT));
  }
}

$xrs = xmlrpc_server_Create();
$cb = function($call, $data) {
  $ca = explode(".",$call);
  try {
    return call_user_func_array(array("lissServer",$ca[1]),$data);
  } catch (lissError $e) {
    return array("faultString"=>$e->getMessage(), "faultCode"=>$e->getCode());
  }
};
$mirror = new ReflectionClass("lissServer");
foreach ($mirror->getMethods() as $method) {
  $name = $method->getName();
  if($name[0] != "_")
    xmlrpc_server_register_method($xrs, "liss.".$method->getName(), $cb);
}
$in = file_get_contents("php://input");
if ($response = xmlrpc_server_call_method($xrs, $in, null)) {
  header('Content-Type: text/xml');
  echo $response;
}

class lissError extends Exception { }