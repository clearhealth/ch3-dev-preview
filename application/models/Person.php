<?php
/*****************************************************************************
*       Person.php
*
*       Author:  ClearHealth Inc. (www.clear-health.com)        2009
*       
*       ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*       respective logos, icons, and terms are registered trademarks 
*       of ClearHealth Inc.
*
*       Though this software is open source you MAY NOT use our 
*       trademarks, graphics, logos and icons without explicit permission. 
*       Derivitive works MUST NOT be primarily identified using our 
*       trademarks, though statements such as "Based on ClearHealth(TM) 
*       Technology" or "incoporating ClearHealth(TM) source code" 
*       are permissible.
*
*       This file is licensed under the GPL V3, you can find
*       a copy of that license by visiting:
*       http://www.fsf.org/licensing/licenses/gpl.html
*       
*****************************************************************************/


class Person extends WebVista_Model_ORM {
	protected $person_id;
	protected $salutation;
	protected $last_name;
	protected $first_name;
	protected $suffix;
	protected $middle_name;
	protected $gender;
	protected $initials;
	protected $date_of_birth;
	protected $summary;
	protected $title;
	protected $notes;
	protected $email;
	protected $secondary_email;
	protected $has_photo;
	protected $identifier;
	protected $identifier_type;
	protected $marital_status;
	protected $inactive;
	protected $active;
	protected $primary_practice_id;
	protected $_table = "person";
	protected $_primaryKeys = array("person_id");
	protected $_legacyORMNaming = true;	

	public function __construct() {
		parent::__construct();
	}

	public function getMiddleInitial() {
		if (strlen($this->middle_name) > 0) {
			return substr($this->middle_name,0,1);
		}
		return "";
	}

	public function getDisplayName() {
		return $this->last_name . ", " . $this->first_name . " " . $this->middle_name;
	}
	public function getDisplayGender() {
		$gender = "";
		switch ($this->gender) {
			case "1":
				$gender = "M";
				break;
			case "2":
				$gender = "F";
				break;
			case "3":
				$gender = "O";
				break;
		}
		return $gender;
	}

	function getAge() {
                if ($this->date_of_birth == '0000-00-00') return '';
		$now = time();
		$dob = strtotime($this->date_of_birth);
                $age = ($dob < 0)? ($now + ($dob * -1)): $now-$dob;
		$year = 60*60*24*365;
		$age = floor($age/$year);
		return $age;
        }
}
