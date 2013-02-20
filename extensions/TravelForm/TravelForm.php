<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['TravelForm'] = 'TravelForm';
$wgExtensionMessagesFiles['TravelForm'] = $dir . 'TravelForm.i18n.php';
$wgSpecialPageGroups['TravelForm'] = 'grand-tools';

function runTravelForm($par) {
	TravelForm::run($par);
}

class TravelForm extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('TravelForm');
		SpecialPage::SpecialPage("TravelForm", HQP.'+', true, 'runTravelForm');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    if(isset($_POST['submit'])){
	    	TravelForm::handleSubmit();
	    }
	    TravelForm::travelForm();
	    //$wgOut->addHTML();
	}
	
	static function handleSubmit(){
		global $wgUser;

		$my_id = $wgUser->getId();
		$curr_year = date("Y");

		$select = "SELECT id FROM grand_travel_forms WHERE user_id={$my_id} AND year={$curr_year}";
		$data = DBFunctions::execSQL($select);
		$data_id = 0;
		if(count($data) > 0 && isset($data[0]['id'])){
			$data_id = $data[0]['id'];
		}
		$preferred_seat = "";
		$gender = "";
		extract($_POST);

		if($data_id){
			$query =<<<EOF
			UPDATE grand_travel_forms
			SET first_name = '{$first_name}', 
				last_name = '{$last_name}', 
				gender = '{$gender}', 
				email = '{$email}', 
				phone_number = '{$phone_number}', 
				dob = '{$dob}', 
				leaving_from = '{$leaving_from}', 
				going_to = '{$going_to}', 
				departure_date = '{$departure_date}', 
				departure_time = '{$departure_time}', 
				return_date = '{$return_date}', 
				return_time = '{$return_time}', 
				preferred_seat = '{$preferred_seat}', 
				preferred_carrier = '{$preferred_carrier}', 
				frequent_flyer = '{$frequent_flyer}', 
				hotel_checkin = '{$hotel_checkin}', 
				hotel_checkout = '{$hotel_checkout}', 
				roommate_preference = '{$roommate_preference}', 
				comments = '{$comments}'
			WHERE id = {$data_id}
EOF;
			$result = DBFunctions::execSQL($query, true);
	
		}else{
			$query =<<<EOF
			INSERT INTO grand_travel_forms(user_id, year, first_name, last_name, gender, email, phone_number, dob, leaving_from, going_to, departure_date, departure_time, return_date, return_time, preferred_seat, preferred_carrier, frequent_flyer, hotel_checkin, hotel_checkout, roommate_preference, comments)
			VALUES('$my_id', '$curr_year', '$first_name', '$last_name', '$gender', '$email', '$phone_number', '$dob', '$leaving_from', '$going_to', '$departure_date', '$departure_time', '$return_date', '$return_time', '{$preferred_seat}', '$preferred_carrier', '$frequent_flyer', '$hotel_checkin', '$hotel_checkout', '$roommate_preference', '$comments')
EOF;
			$result = DBFunctions::execSQL($query, true);
		}
	}




	static function travelForm(){
		global $wgOut, $wgScriptPath, $wgServer, $wgUser;

		$my_id = $wgUser->getId();
		$me = Person::newFromId($wgUser->getId());
		$first_name = $me->getFirstName();
		$last_name = $me->getLastName();
		$email = $me->getEmail();
		$gender = $me->getGender();
		$phone_number = $dob = $leaving_from = $going_to = $departure_date = $departure_time = $return_date = $return_time = $preferred_seat = $preferred_carrier = $frequent_flyer = $hotel_checkin = $hotel_checkout = $roommate_preference = $comments = "";

		$curr_year = date("Y");

		$query = "SELECT * FROM grand_travel_forms WHERE user_id={$my_id} AND year={$curr_year} LIMIT 1";
		$data = DBFunctions::execSQL($query);

		if(count($data) > 0){
            $row = $data[0];
            $first_name = (isset($row['first_name']))? $row['first_name'] : $first_name;
            $last_name = (isset($row['last_name']))? $row['last_name'] : $last_name;
            $email = (isset($row['email']))? $row['email'] : $email;
            $phone_number = (isset($row['phone_number']))? $row['phone_number'] : $phone_number;
            $gender = (isset($row['gender']))? $row['gender'] : $gender;
            $dob = (isset($row['dob']))? $row['dob'] : $dob;
            $leaving_from = (isset($row['leaving_from']))? $row['leaving_from'] : $leaving_from;
            $going_to = (isset($row['going_to']))? $row['going_to'] : $going_to;
            $departure_date = (isset($row['departure_date']))? $row['departure_date'] : $departure_date;
            $departure_time = (isset($row['departure_time']))? $row['departure_time'] : $departure_time;
            $return_date = (isset($row['return_date']))? $row['return_date'] : $return_date;
            $return_time = (isset($row['return_time']))? $row['return_time'] : $return_time;

            $preferred_seat = (isset($row['preferred_seat']))? $row['preferred_seat'] : $preferred_seat;
            $preferred_carrier = (isset($row['preferred_carrier']))? $row['preferred_carrier'] : $preferred_carrier;
            $frequent_flyer = (isset($row['frequent_flyer']))? $row['frequent_flyer'] : $frequent_flyer;

            $hotel_checkin = (isset($row['hotel_checkin']))? $row['hotel_checkin'] : $hotel_checkin;
            $hotel_checkout = (isset($row['hotel_checkout']))? $row['hotel_checkout'] : $hotel_checkout;
            $roommate_preference = (isset($row['roommate_preference']))? $row['roommate_preference'] : $roommate_preference;

            $comments = (isset($row['comments']))? $row['comments'] : $comments;
        }

        $male_checked = $female_checked = $aisle_checked = $middle_checked = $window_checked = "";
        if($gender == 'M'){
        	$male_checked = "checked='checked'";
        }
        else if($gender == 'F'){
        	$female_checked = "checked='checked'";
        }

        if($preferred_seat == 'Aisle'){
        	$aisle_checked = "checked='checked'";
        }
        else if($preferred_seat == 'Middle'){
        	$middle_checked = "checked='checked'";
        }
        else if($preferred_seat == 'Window'){
        	$window_checked = "checked='checked'";
        }

		$html =<<<EOF
			<script type="text/javascript">
			$(function() {
    			$( "#departure_date, #return_date, #hotel_checkout, #hotel_checkin" ).datepicker();
  				$( "#dob").datepicker({
  					changeMonth: true,
      				changeYear: true,
      				yearRange: "1920:2000"
      			});
  			});
			</script>
			<style type="text/css">
			td.label {
				width: 200px;
				background-color: #F3EBF5;
				vertical-align: middle;
			}
			td input[type=text]{
				width: 240px;
			}
			td textarea {
				height: 150px;
			}
			</style>
			<h3>Travel Information</h3>
			<form action='$wgServer$wgScriptPath/index.php/Special:TravelForm' method='post'>
			
			<table width='100%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<td class='label'>First Name</td><td><input type='text' name='first_name' value='{$first_name}' /></td>
			<td class='label'>Phone Number</td><td><input type='text' name='phone_number' value='{$phone_number}' /></td>
			</tr>
			<tr>
			<td class='label'>Last Name</td><td><input type='text' name='last_name' value='{$last_name}'/></td>
			<td class='label'>Gender</td>
			<td>
			<span style="white-space: nowrap;">Male <input type='radio' name='gender' value='M' {$male_checked} />&nbsp;&nbsp;Female <input type='radio' name='gender' value='F' {$female_checked} />
			</span>
			</td>
			</tr>
			<tr>
			<td class='label'>Email Address</td><td><input type='text' name='email' value='{$email}' /></td>
			<td class='label'>Date of Birth</td><td><input type='text' id='dob' name='dob' value='{$dob}' /></td>
			</tr>
			</table>
			<br />
			<table width='50%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr><td class='label'>Leaving from (airport)</td><td><input type='text' name="leaving_from" value='{$leaving_from}' /></td></tr>
			<tr><td class='label'>Going to (airport)</td><td><input type='text' name="going_to" value='{$going_to}' /></td></tr>
			<tr><td class='label'>Departure Date</td><td><input type='text' id="departure_date" name="departure_date" value='{$departure_date}' /></td></tr>
			<tr><td class='label'>Departure Time</td><td><input type='text' name="departure_time" value='{$departure_time}' /></td></tr>
			<tr><td class='label'>Return Date</td><td><input type='text' id="return_date" name="return_date" value='{$return_date}' /></td></tr>
			<tr><td class='label'>Return Time</td><td><input type='text' name="return_time" value='{$return_time}' /></td></tr>
			</table>
			<br />
			<table width='50%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr><td class='label'>Preferred Seat</td>
			<td>
			<span style="white-space: nowrap;">
			Aisle <input type='radio' name='preferred_seat'  value='Aisle' {$aisle_checked} />
			&nbsp;&nbsp;
			Middle <input type='radio' name='preferred_seat' value='Middle' {$middle_checked} />
			&nbsp;&nbsp;
			Window <input type='radio' name='preferred_seat' value='Window' {$window_checked} />
			</span>
			</td>
			</tr>
			<tr><td class='label'>Preferred Carrier</td><td><input type='text' name="preferred_carrier" value='{$preferred_carrier}' /></td></tr>
			<tr><td class='label'>Frequent Flyer Number</td><td><input type='text' name="frequent_flyer" value='{$frequent_flyer}' /></td>
			</tr>
			</table>
			<br />
			<table width='50%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr><td class='label'>Hotel Check-in Date</td><td><input type='text' id="hotel_checkin" name="hotel_checkin" value='{$hotel_checkin}' /></td></tr>
			<tr><td class='label'>Hotel Check-out Date</td><td><input type='text' id="hotel_checkout" name="hotel_checkout" value='{$hotel_checkout}' /></td></tr>
			<tr><td class='label'>Roommate Preference</td><td><input type='text' name="roommate_preference" value='{$roommate_preference}' /></td></tr>
			</table>
			<br />
			<table width='80%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr><td class='label'>Comments</td><td><textarea name="comments">{$comments}</textarea></td></tr>
			</table>
			<br />
			<input type='submit' name='submit' value='Submit'>
			</form>
EOF;

		$wgOut->addHTML($html);
	}
}

?>
