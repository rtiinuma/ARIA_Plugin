<?php

/**
 * The file that defines the functionality for handling competition registration.
 *
 * A class definition that includes attributes and functions that allow the
 * registration (of students and teachers) for NNMTA competitions to operate
 * seamlessly.
 *
 * @link       http://wesleykepke.github.io/ARIA/
 * @since      1.0.0
 *
 * @package    ARIA
 * @subpackage ARIA/includes
 */

require_once("class-aria-api.php");
require_once("class-aria-create-master-forms.php");
require_once("aria-constants.php");

/**
 * The competition registration handler class.
 *
 * @since      1.0.0
 * @package    ARIA
 * @subpackage ARIA/includes
 * @author     KREW
*/
class ARIA_Registration_Handler {

	/**
	 * Function for sending emails.
	 */
  public static function aria_send_registration_emails($teacher_hash, $teacher_url, $teacher_email, $student_hash) {
    // this is going to need to iterate through a given teachers array
    // of students and generate a url that has that specific teacher's hash
    // and a hash for each of the students involved in the competition
    // a sample url with 2 hashes might look like the following:
    // wesley-bruh-teacher-registration-4/?teacher_hash=fredharris&student_hash=weskepke
    // note the & in between the two hash values

    // or, this function can be called everytime a student submits their data:
/*
    $teacher_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $teacher_link .= "?teacher_hash=" . $teacher_hash;
    $teacher_link .= "&student_hash=" . $student_hash;
*/
    $teacher_url .= "?teacher_hash=" . $teacher_hash;
    $teacher_url .= "&student_hash=" . $student_hash;

    $message = "Congratulations. One of your students has registered for an NNMTA";
    $message .= " music competition. Please click on the following link to finish";
    $message .= " registering your student: " . $teacher_url;

    $subject = "NNMTA Music Competition - Registration";

    if (!wp_mail((string)$teacher_email, $subject, $message)) {
      wp_die('Teacher registration email failed to send.');
    }

  }

	/**
	 * Function for returning related forms.
	 *
	 * This function will return an associative array that maps the titles of
	 * the associated forms in a music competition (student, student master,
	 * teacher, and teacher master) to their respective form IDs.
	 *
	 * @param $prepended_title	String	The prepended portion of the competition title.
	 *
	 * @author KREW
	 * @since 1.0.0
	 */
	public static function aria_find_related_forms_ids($prepended_title) {
		// make sure to get all forms! check this
		$all_forms = GFAPI::get_forms();

		$form_ids = array(
			STUDENT_FORM => null,
			STUDENT_MASTER => null,
			TEACHER_FORM => null,
			TEACHER_MASTER => null
		);

		$student_form = $prepended_title . " Student Registration";
		$student_master_form = $prepended_title . " Student Master";
		$teacher_form = $prepended_title . " Teacher Registration";
		$teacher_master_form = $prepended_title . " Teacher Master";
		$all_competition_forms = array($student_form, $student_master_form,
      $teacher_form, $teacher_master_form);

		foreach ($all_forms as $form) {
			switch ($form["title"]) {
				case $student_form:
					$form_ids[self::STUDENT_FORM] = $form["id"];
					break;

				case $student_master_form:
					$form_ids[self::STUDENT_MASTER] = $form["id"];
					break;

				case $teacher_form:
						$form_ids[self::TEACHER_FORM] = $form["id"];
					break;

				case $teacher_master_form:
					$form_ids[self::TEACHER_MASTER] = $form["id"];
					break;

				default:
					break;
			}
		}

		// make sure all forms exist
		foreach ($form_ids as $key => $value) {
			if (!isset($value)) {
				wp_die('Error: The form titled ' . $all_competition_forms[$key] .
				" does not exist.");
			}
		}
		return $form_ids;
	}

	/**
	 * Function for searching through student-master to find a student.
   *
   * This function will search through the student-master form and check to see
   * if a particular student exists. If a studetn exists within the student-
   * master form of a particular competition, then the entry for that
   * student will be returned. Otherwise, if no such student exists, the
   * function will return false.
   *
   * @param $student_master_form_id   Integer   The ID of the student-master form.
   * @param $student_hash             String    The hash of a particular student.
   *
   * @since 1.0.0
   * @author KREW
	 */
  public static function aria_find_student_entry($student_master_form_id, $student_hash) {
    $hash_field_id = ARIA_API::aria_master_student_field_id_array()['hash'];

    // check to see if any of the entries in the student master have $student_hash
    $search_criteria = array(
      'field_filters' => array(
        'mode' => 'any',
        array(
         'key' => (string) $hash_field_id,
         'value' => $student_hash
        )
      )
    );

    $entries = GFAPI::get_entries($student_master_form_id, $search_criteria);
    if(count($entries) == 1 && rgar($entries[0], (string) $hash_field_id) == $student_hash) {
     return $entries[0];
    }

    return false;
  }

	/**
	 * Function for searching through teacher-master to find a teacher.
   *
   * This function will search through the teacher-master form and check to see
   * if a particular teacher exists. If a teacher exists within the teacher
   * master form of a particular competition, then the entry for that
   * teacher will be returned. Otherwise, if no such teacher exists, the
   * function will return false.
   *
   * @param $teacher_master_form_id   Integer   The ID of the teacher master form.
   * @param $teacher_hash             String    The hash of a particular teacher.
   *
   * @since 1.0.0
   * @author KREW
	 */
  public static function aria_find_teacher_entry($teacher_master_form_id, $teacher_hash) {

//wp_die('teacher hash inside find: ' . $teacher_hash);

    $hash_field_id = ARIA_API::aria_master_teacher_field_id_array()['teacher_hash'];

    // check to see if any of the entries in the teacher master have $teacher_hash
    $search_criteria = array(
			'field_filters' => array(
				'mode' => 'all',
				array(
					'key' => (string) $hash_field_id,
					'value' => $teacher_hash
				)
			)
		);

    $entries = GFAPI::get_entries($teacher_master_form_id, $search_criteria);

//wp_die(print_r($entries));

    if (count($entries) === 1 && rgar($entries[0], (string) $hash_field_id) == $teacher_hash) {
      // it's reaching this wp_die()
      //wp_die("After get_entries, inside if statement: " . print_r($entries));
      return $entries[0];
    }

    return false;
  }

	/**
	 * Function to check if a student is assigned to a teacher.
	 */
   public static function aria_check_student_teacher_relationship($related_forms, $student_hash, $teacher_hash) {
     // Get field ids
		 $students_field_id = ARIA_API::aria_master_teacher_field_id_array()['students'];

     // Get the teacher entry
     $teacher_entry = self::aria_find_teacher_entry($related_forms['teacher_master_form_id'], $teacher_hash);

     // return if teacher entry does not exist.
     if($teacher_entry == false) return false;

     // get the array of students the teacher is assigned.
     $students = unserialize(rgar($teacher_entry, (string) $students_field_id));

     // find the student name in the array of students.
     foreach($students as $student) {
       if ($student == $student_hash) return true;
     }
     return false;
   }

	/**
	 * Function to get pre-populate values based on teacher-master.
	 */
	 public static function aria_get_teacher_pre_populate($related_forms, $teacher_hash) {
		 $hash_field_id = ARIA_API::aria_master_teacher_field_id_array()['hash'];

		 $search_criteria = array(
       'field_filters' => array(
         'mode' => 'any',
         array(
           'key' => (string) $hash_field_id,
           'value' => $teacher_hash
         )
       )
		 );

		 $entries = GFAPI::get_entries($related_forms['teacher_master_form_id'], $search_criteria);

		 if (is_wp_error($entries)) {
 			wp_die($entries->get_error_message());
 		}

		$field_ids = ARIA_API::aria_master_teacher_field_id_array();

		return array(
			'name' => rgar( $entries[0], (string) $field_ids['name'] ),
			'email' => rgar( $entries[0], (string) $field_ids['email'] ),
			'phone' => rgar( $entries[0], (string) $field_ids['phone'] ),
			'volunteer_preference' => rgar( $entries[0], (string) $field_ids['volunteer_preference'] ),
			'volunteer_time' => rgar( $entries[0], (string) $field_ids['volunteer_time'] ),
			'students' => rgar( $entries[0], (string) $field_ids['students'] ),
			'is_judging' => rgar( $entries[0], (string) $field_ids['is_judging'] ),
			'hash' => rgar( $entries[0], (string) $field_ids['hash'])

		);
	 }

	/**
	 * Function to get pre-populate values based on student-master.
	 */
	 public static function aria_get_student_pre_populate($related_forms, $student_hash) {
		 $hash_field_id = ARIA_API::aria_master_student_field_id_array()['hash'];

		 $search_criteria = array(
       'field_filters' => array(
         'mode' => 'any',
         array(
           'key' => (string) $hash_field_id,
           'value' => $student_hash
         )
       )
		 );

		 $entries = GFAPI::get_entries($related_forms['student_master_form_id'], $search_criteria);

		 if (is_wp_error($entries)) {
 			wp_die($entries->get_error_message());
 		}

		$field_ids = ARIA_API::aria_master_student_field_id_array();

		return array(
			'parent_name' => rgar( $entries[0], (string) $field_ids['parent_name']),
	    'parent_email' => rgar( $entries[0], (string) $field_ids['parent_email']),
	    'student_name' => rgar( $entries[0], (string) $field_ids['student_name']),
	    'student_birthday' => rgar( $entries[0], (string) $field_ids['student_birthday']),
	    'teacher_name' => rgar( $entries[0], (string) $field_ids['teacher_name']),
	    'not_listed_teacher_name' => rgar( $entries[0], (string) $field_ids['not_listed_teacher_name']),
	    'available_festival_days' => rgar( $entries[0], (string) $field_ids['available_festival_days']),
	    'preferred_command_performance' => rgar( $entries[0], (string) $field_ids['preferred_command_performance']),
	    'song_1_period' => rgar( $entries[0], (string) $field_ids['song_1_period']),
	    'song_1_composer' =>  rgar( $entries[0], (string) $field_ids['song_1_composer']),
	    'song_1_selection' =>  rgar( $entries[0], (string) $field_ids['song_1_selection']),
	    'song_2_period' =>  rgar( $entries[0], (string) $field_ids['song_2_period']),
	    'song_2_composer' =>  rgar( $entries[0], (string) $field_ids['song_2_composer']),
	    'song_2_selection' =>  rgar( $entries[0], (string) $field_ids['song_2_selection']),
	    'theory_score' =>  rgar( $entries[0], (string) $field_ids['theory_score']),
	    'alternate_theory' =>  rgar( $entries[0], (string) $field_ids['alternate_theory']),
	    'competition_format' =>  rgar( $entries[0], (string) $field_ids['competition_format']),
	    'timing_of_pieces' =>  rgar( $entries[0], (string) $field_ids['timing_of_pieces']),
			'hash' => rgar( $entries[0], (string) $field_ids['hash'])
		);
	}

}
