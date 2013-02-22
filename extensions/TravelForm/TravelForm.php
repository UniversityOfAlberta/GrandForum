<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['TravelForm'] = 'TravelForm';
$wgExtensionMessagesFiles['TravelForm'] = $dir . 'TravelForm.i18n.php';
$wgSpecialPageGroups['TravelForm'] = 'grand-tools';

require_once($dir . '../../Classes/PHPExcel/IOFactory.php');

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

	static function sendEmail($resubmission){
		global $wgUser;

		$my_id = $wgUser->getId();
		$curr_year = date("Y");
		$select = "SELECT * FROM grand_travel_forms WHERE user_id={$my_id} AND year={$curr_year}";
		$data = DBFunctions::execSQL($select);
		$success = false;

		if(count($data) > 0 && isset($data[0]['id'])){
			$row = $data[0];
			$first_name = (isset($row['first_name']))? $row['first_name'] : "N/A";
            $last_name = (isset($row['last_name']))? $row['last_name'] : "N/A";
            $email = (isset($row['email']))? $row['email'] : "N/A";
            $phone_number = (isset($row['phone_number']))? $row['phone_number'] : "N/A";
            $gender = (isset($row['gender']))? $row['gender'] : "N/A";
            $dob = (isset($row['dob']))? $row['dob'] : "N/A";
            $leaving_from = (isset($row['leaving_from']))? $row['leaving_from'] : "N/A";
            $going_to = (isset($row['going_to']))? $row['going_to'] : "N/A";
            $departure_date = (isset($row['departure_date']))? $row['departure_date'] : "N/A";
            $departure_time = (isset($row['departure_time']))? $row['departure_time'] : "N/A";
            $return_date = (isset($row['return_date']))? $row['return_date'] : "N/A";
            $return_time = (isset($row['return_time']))? $row['return_time'] : "N/A";

            $preferred_seat = (isset($row['preferred_seat']))? $row['preferred_seat'] : "N/A";
            $preferred_carrier = (isset($row['preferred_carrier']))? $row['preferred_carrier'] : "N/A";
            $frequent_flyer = (isset($row['frequent_flyer']))? $row['frequent_flyer'] : "N/A";

            $hotel_checkin = (isset($row['hotel_checkin']))? $row['hotel_checkin'] : "N/A";
            $hotel_checkout = (isset($row['hotel_checkout']))? $row['hotel_checkout'] : "N/A";
            $roommate_preference = (isset($row['roommate_preference']))? $row['roommate_preference'] : "N/A";

            $comments = (isset($row['comments']))? $row['comments'] : "N/A";
            
            $fields = array(
            	"First name"=> $first_name,
				"Last name"=> $last_name,
				"Email"=> $email,
				"Phone Number"=> $phone_number,
				"Gender"=> $gender,
				"Date of Birth"=> $dob,
				"Leaving from (airport)"=> $leaving_from,
				"Going to (airport)"=> $going_to,
				"Departure Date"=> $departure_date,
				"Departure Time"=> $departure_time,
				"Return Date"=> $return_date,
				"Return Time"=> $return_time,
				"Preferred Seat"=> $preferred_seat,
				"Preferred Carrier"=> $preferred_carrier,
				"Frequent Flyer Number"=> $frequent_flyer,
				"Hotel Check-in Date"=> $hotel_checkin,
				"Hotel Check-out Date"=> $hotel_checkout,
				"Roommate Preference"=> $roommate_preference,
				"Comments"=> $comments
            );
            //EXCEL
            $phpExcel = new PHPExcel();
			$styleArray = array(
				'font' => array(
					'bold' => true,
				)
			);
			 
			//Get the active sheet and assign to a variable
			$foo = $phpExcel->getActiveSheet();
			 
			//add column headers, set the title and make the text bold
			$foo->setCellValue("A1", "Field")
				->setCellValue("B1", "Value")
				->setTitle("Travel Information")
				->getStyle("A1:B1")->applyFromArray($styleArray);
			$row_count = 2;
			foreach($fields as $label=>$value){
				$foo->setCellValue("A{$row_count}", $label)->setCellValue("B{$row_count}", $value);
				$row_count++;
			}	
			
			$foo->getColumnDimension("A")->setWidth(40);
			$foo->getColumnDimension("B")->setWidth(40);		
			$phpExcel->setActiveSheetIndex(0);

			ob_start();
			$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, "Excel5");
			$objWriter->save("php://output");
			$excel_content = ob_get_contents();
			ob_end_clean();

            //EMAIL
			$title = "Submission";
            if($resubmission){
            	$title = "Re-Submission";
            }

            $email_body =<<<EOF
New GRAND Forum Travel Form {$title}!\n
Travel Information:\n
EOF;
			foreach($fields as $label=>$value){
				if($label == "Comments"){
					$email_body .= "{$label}:\n{$value}\n";
				}
				else{
					$email_body .= "{$label}: {$value}\n";
				}
			}
			$email_body .=<<<EOF
Regards,
GRAND Forum
support@forum.grand-nce.ca
EOF;

			$to = "fauve_mackenzie@gnwc.ca"; 
			$cc = $email;
			$subject = "Travel Form {$title}: $first_name $last_name";
			$from = "GRAND Forum <support@forum.grand-nce.ca>";
			$filename = "{$last_name}_{$first_name}.xls";
			if($resubmission){
				$filename = "Resubmission-{$last_name}_{$first_name}.xls";
			}

			$success = TravelForm::mail_attachment($excel_content, $filename, $to, $cc, $from, $subject, $email_body);
		}
		return $success;
	}

	static function mail_attachment($content, $filename, $to, $cc, $from, $subject, $message) {
	    
	    $content = chunk_split(base64_encode($content));
	    $uid = md5(uniqid(time()));
	   
	    $header = "From: ".$from."\r\n";
	    $header .= "Cc: ".$cc."\r\n";
	    $header .= "Reply-To: ".$from."\r\n";
	    $header .= "MIME-Version: 1.0\r\n";
	    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	    $header .= "This is a multi-part message in MIME format.\r\n";
	    $header .= "--".$uid."\r\n";
	    $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	    $header .= $message."\r\n\r\n";
	    $header .= "--".$uid."\r\n";
	    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
	    $header .= "Content-Transfer-Encoding: base64\r\n";
	    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	    $header .= $content."\r\n\r\n";
	    $header .= "--".$uid."--";
	    if (mail($to, $subject, "", $header)) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	static function handleSubmit(){
		global $wgUser, $wgMessage;

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
		$post_vars = array('first_name', 'last_name', 'email', 'phone_number', 'dob', 'leaving_from', 'going_to', 'departure_date', 'departure_time', 'return_date', 'return_time', 'preferred_carrier', 'frequent_flyer', 'hotel_checkin', 'hotel_checkout', 'roommate_preference', 'comments');
		//extract($_POST);
		foreach($post_vars as $var){
			$$var = filter_var($_POST[$var], FILTER_SANITIZE_STRING);
			//echo "$var = ".$$var ."<br>";
		}
		$gender = (isset($_POST['gender']))? $_POST['gender'] : "";
		$preferred_seat = (isset($_POST['preferred_seat']))? $_POST['preferred_seat'] : "";
		
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
		$success = TravelForm::sendEmail($data_id);
		if($success){
			$wgMessage->addSuccess("Thank you! Your Travel Form has been successfully submitted! ");
		}
		else{
			$wgMessage->addError("There was a problem with submitting the form. If the problem persists, please contact support@forum.grand-nce.ca.");
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
			<script language="javascript" type="text/javascript" src="$wgServer$wgScriptPath/scripts/jquery.validate.min.js"></script>
			<script type="text/javascript">
			jQuery.validator.addMethod("greaterThan", 
				function(value, element, params) {

				    if (!/Invalid|NaN/.test(new Date(value))) {
				        return new Date(value) >= new Date($(params).val());
				    }

				    return isNaN(value) && isNaN($(params).val()) 
				        || (Number(value) > Number($(params).val())); 
				},'Must be greater than Departure Date.');
			jQuery.validator.addMethod("greaterThan2", 
				function(value, element, params) {

				    if (!/Invalid|NaN/.test(new Date(value))) {
				        return new Date(value) > new Date($(params).val());
				    }

				    return isNaN(value) && isNaN($(params).val()) 
				        || (Number(value) >= Number($(params).val())); 
				},'Must be greater than Check-in Date.');
			
			$(function() {
    			$( "#departure_date, #return_date, #hotel_checkout, #hotel_checkin" ).datepicker();
  				$( "#dob").datepicker({
  					changeMonth: true,
      				changeYear: true,
      				yearRange: "1920:2000"
      			});
				
				$("#travelForm").validate({
				    rules: {
				        return_date: { greaterThan: "#departure_date" },
				        hotel_checkout: { greaterThan2: "#hotel_checkin" },
				        hotel_checkin: { greaterThan: "#departure_date" },
				    }
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
			label.error { 
				float: none; 
				color: red;  
				vertical-align: top; 
				display: block;
				background: none;
				padding: 0 0 0 5px;
				margin: 2px;
				width: 240px;
			}
			input.error {
				background: none;
				background-color: #FFF !important;
				padding: 3px 3px;
				margin: 2px;
			}
			span.requ {
				font-weight:bold;
				color: red;
			}
			</style>
			<h3>Travel Information</h3>
			<form id="travelForm" action='$wgServer$wgScriptPath/index.php/Special:TravelForm' method='post'>
			
			<table width='100%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr>
			<td class='label'><span class="requ">*</span>First Name</td><td><input type='text' class="required" name='first_name' value='{$first_name}' /></td>
			<td class='label'>Phone Number</td><td><input type='text' name='phone_number' value='{$phone_number}' /></td>
			</tr>
			<tr>
			<td class='label'><span class="requ">*</span>Last Name</td><td><input type='text' class="required" name='last_name' value='{$last_name}'/></td>
			<td class='label'>Gender</td>
			<td>
			<span style="white-space: nowrap;">Male <input type='radio' name='gender' value='M' {$male_checked} />&nbsp;&nbsp;Female <input type='radio' name='gender' value='F' {$female_checked} />
			</span>
			</td>
			</tr>
			<tr>
			<td class='label'><span class="requ">*</span>Email Address</td><td><input type='text' class="required email" name='email' value='{$email}' /></td>
			<td class='label'>Date of Birth</td><td><input type='text' id='dob' name='dob' value='{$dob}' /></td>
			</tr>
			</table>
			<br />
			<table width='50%' class="wikitable" cellspacing="1" cellpadding="5" frame="box" rules="all">
			<tr><td class='label'><span class="requ">*</span>Leaving from (airport)</td><td><input type='text' class="required" name="leaving_from" value='{$leaving_from}' /></td></tr>
			<tr><td class='label'><span class="requ">*</span>Going to (airport)</td><td><input type='text' class="required" name="going_to" value='{$going_to}' /></td></tr>
			<tr><td class='label'><span class="requ">*</span>Departure Date</td><td><input type='text' id="departure_date" class="required" name="departure_date" value='{$departure_date}' /></td></tr>
			<tr><td class='label'>Departure Time</td><td><input type='text' name="departure_time" value='{$departure_time}' /></td></tr>
			<tr><td class='label'><span class="requ">*</span>Return Date</td><td><input type='text' id="return_date" class="required" name="return_date" value='{$return_date}' /></td></tr>
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
			<tr><td class='label'><span class="requ">*</span>Hotel Check-in Date</td><td><input type='text' class="required" id="hotel_checkin" name="hotel_checkin" value='{$hotel_checkin}' /></td></tr>
			<tr><td class='label'><span class="requ">*</span>Hotel Check-out Date</td><td><input type='text' class="required" id="hotel_checkout" name="hotel_checkout" value='{$hotel_checkout}' /></td></tr>
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
