<?php
/**
* @desc Prints an actual wwassignment with an iframe to WeBWorK.
*/

require_once("../../config.php");
require_once("locallib.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // NEWMODULE ID

if($id) {
    if (! $cm = get_record("course_modules", "id", $id)) {
        error("Course Module ID was incorrect");
    }
    
    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }
    if (! $wwassignment = get_record("wwassignment", "id", $cm->instance)) {
        error("Course module is incorrect");
    }
} else {
    
    if (! $wwassignment = get_record("wwassignment", "id", $a)) {
        error("Course module is incorrect");
    }
    if (! $course = get_record("course", "id", $wwassignment->course)) {
        error("Course is misconfigured");
    }
    if (! $cm = get_coursemodule_from_instance("wwassignment", $wwassignment->id, $course->id)) {
        error("Course Module ID was incorrect");
    }  
}

//catch the guests
global $USER;
if($USER->username == 'guest') {  # this allows guests to view webwork (signed in as user guest)
    #FIXME  -- replace this with a method that uses the automatic guest sign in on webwork.
    // print_error('Guests cannot view WeBWorK Problem Sets');
}

//force login
$courseid = $course->id;
$wwassignmentid = $wwassignment->id;
require_login($courseid);

//webwork code
$wwcoursename = _wwassignment_mapped_course($courseid,false);
$wwusername = $USER->username;
$wwsetname = $wwassignment->webwork_set;
_wwassignment_mapcreate_user($wwcoursename,$wwusername);
_wwassignment_mapcreate_user_set($wwcoursename,$wwusername,$wwsetname);

$wwkey = _wwassignment_login_user($wwcoursename,$wwusername);
$wwsetlink = _wwassignment_link_to_set_auto_login($wwcoursename,$wwsetname,$wwusername,$wwkey);

add_to_log($course->id, "wwassignment", "view", "view.php?id=$cm->id", "$wwassignmentid",_wwassignment_cmid());

/// Print the page header

if ($course->category) {
    $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
}

$strwwassignments = get_string("modulenameplural", "wwassignment");
$strwwassignment  = get_string("modulename", "wwassignment");

print_header("$course->shortname: $wwassignment->name", "$course->fullname", "$navigation <a href='index.php?id=$course->id'>$strwwassignments</a> -> $wwassignment->name", "", "", true, update_module_button($cm->id, $course->id, $strwwassignment), navmenu($course, $cm));
/// Print the main part of the page



// Print webwork in iframe and link to escape to have webwork in a single window
print("<p style='font-size: smaller; color: #aaa;'>" . get_string("iframeNoShow-1", "wwassignment")
      . "<a href='$wwsetlink'>" . get_string("iframeNoShow-2", "wwassignment")
      ."</a><p align='center'></iframe></p>\n"
      );
print("<iframe id='wwPage' src='$wwsetlink' frameborder='1' "
      . "width='".$CFG->wwassignment_iframewidth."' "
      . "height='".$CFG->wwassignment_iframeheight."'>"
      );

print("<script>ww.Init(".isteacher($course->id).")</script>");


/// Finish the page
print_footer($course);

?>
