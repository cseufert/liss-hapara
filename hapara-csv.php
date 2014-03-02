<?php
$school = "mckinnonsc.vic.edu.au";
$classlist = json_decode(file_get_contents("data/classlist.$school.json"),true);
$classstud = json_decode(file_get_contents("data/classmember.$school.json"), true);
$timetable = json_decode(file_get_contents("data/timetable.$school.json"), true);

$classMember = array();
foreach($classlist as $class)
  $currentClass[$class['EdvalClassCode']] = array();

foreach($classstud as $member)
  if(preg_match("/^[A-Za-z-]{3}[0-9]{4}$/",$member["StudentId"]))
    if(isset($currentClass[$member["EdvalClassCode"]]))
      $classMember[$member['StudentId']][] = $member["EdvalClassCode"];

$f = fopen("tmp/hapara-students.$school.csv", "w");
fwrite($f, "email,class code\r\n");
foreach($classMember as $k=>$v) {
  fwrite($f, $k.",\"".implode(",",$v)."\"\r\n");
}
fclose($f);

$tdclass = array();
foreach($classlist as $class) {
  if(isset($class["DefaultTeacher"]) && $class["DefaultTeacher"])
    $tdclass[$class["EdvalClassCode"]] = array($class["DefaultTeacher"]);
  else
    $tdclass[$class["EdvalClassCode"]] = array();
}
foreach($timetable as $class) {
  if(!in_array($class["TeacherCode"],$tdclass[$class["EdvalClassCode"]]))
    $tdclass[$class["EdvalClassCode"]][] = $class["TeacherCode"];
}

$f = fopen("tmp/hapara-subjects.$school.csv","w");
fwrite($f, "Mailbox,Name,Teacher,Subject Folders\r\n");
foreach($tdclass as $code=>$teacher) {
  if($teacher)
    fwrite($f, "$code-2014,$code,\"".implode(",",$teacher)."\",$code\r\n");
}
fclose($f);