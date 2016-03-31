<?php

/**
 * The time block object used for scheduling.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    ARIA
 * @subpackage ARIA/admin
 */

require_once(ARIA_ROOT . "/includes/class-aria-api.php");
require_once(ARIA_ROOT . "/admin/scheduler/class-aria-section.php");

/**
 * The time block object used for scheduling.
 *
 * This class defines a time block object, which will be used throughout the
 * scheduling process. This object will represent a given time block in a
 * competition, which can be considered to be a block of time (9:00 - 9:45, for
 * example) that students will be scheduled in. For each time block, there will
 * be an arbitrary number of concurrent sections that will be occuring
 * simultaneously (the number of concurrent sections is determined by the
 * festival chairman). These concurrent sections may be of different types
 * (master, traditional, etc.) and even of different skill levels (1-11).
 *
 * @package    ARIA
 * @subpackage ARIA/admin
 * @author     KREW
 */
class TimeBlock {

  /**
   * The number of concurrent sections per time block (determined by the
   * festival chairman).
   *
   * @since 1.0.0
   * @access private
   * @var 	int 	$num_concurrent_sections 	The number of concurrent sections.
   */
  private $num_concurrent_sections;

  /**
   * The array of section objects per time block.
   *
   * @since 1.0.0
   * @access private
   * @var 	array 	$sections 	The concurrent section objects.
   */
  private $sections;

  /**
   * The constructor used to instantiate a new time block object.
   *
   * @since 1.0.0
   * @param	int 	$num_concurrent_sections 	The number of concurrent sections.
   * @param	int 	$time_block_duration 	The length of the concurrent sections.
   */
  function __construct($num_concurrent_sections, $time_block_duration) {
    $this->num_concurrent_sections = $num_concurrent_sections;
    $this->sections = new SplFixedArray($num_concurrent_sections);
    for ($i = 0; $i < $num_concurrent_sections; $i++) {
      $this->sections[$i] = new Section($time_block_duration);
    }
  }

  /**
   * The function will attempt to schedule a student in the current time block.
   *
   * This function will iterate over all of the section objects in the current
   * time block and attempt to add the incoming student to one of the sections.
   * This function will return true if the given student object was added to
   * a section in the current time block and false otherwise.
   *
   * @since 1.0.0
   * @param	Student	$student	The student that needs to be scheduled.
   *
   * @return true if the student was added, false otherwise
   */
  public function schedule_student($student) {
    for ($i = 0; $i < $this->num_concurrent_sections; $i++) {
      if ($this->sections[$i]->add_student($student)) {
        return true;
      }
    }

    return false;
  }

  /**
   * This function will assign a section within the current time block to be a
   * master-class section.
   *
   * @return true if section was designated as a master-class section, false otherwise
   */
  public function assign_section_to_master() {
    for ($i = 0; $i < $this->num_concurrent_sections; $i++) {
      if ($this->sections[$i]->assign_section_to_master()) {
        return true;
      }
    }

    return false;
  }

  /**
   * This function will print the sections in a given time block object.
   */
  public function print_schedule() {
    for ($i = 0; $i < $this->num_concurrent_sections; $i++) {
      echo '<b>Section # ' . $i . '</b><br>';
      $this->sections[$i]->print_schedule();
      echo '<br>';
    }
  }

  /**
   * The destructor used when a time block object is destroyed.
   *
   * @since 1.0.0
   */
  function __destruct() {
    unset($this->num_concurrent_sections);
    unset($this->sections);
  }
}