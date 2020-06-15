<?php

//for converting back to json
class vector implements JsonSerializable
{
	public function __construct(array $arr)
	{
		$this->array = $arr;
	}

	public function jsonSerialize()
	{
		return $this->array;
	}
} 

//connect to the json file
$connection = ssh2_connect('COMPANYSITE',80);
ssh2_auth_password($connection,'USERNAME','PASSWORD');

//establish connection
$sftp = ssh2_sftp($connection);
$sftp_fd = "ssh2.sftp://$sftp/wp-content/themes/mytheme/shippingHoliday.json";

if (array_key_exists('submit',$_POST)) //if submitted
{
	//save back to json
	$holidays = explode(",",$_POST['holidays']);
	$cutoff = $_POST['cutOffInput'];
	$arr = [
		"cutoverTime" => $cutoff,
		"holidays" => $holidays
	];
	file_put_contents($sftp_fd, json_encode(new vector($arr), JSON_PRETTY_PRINT));
	
	echo "Submitted";

}

//get json
$handle = fopen($sftp_fd,'r');
$json = json_decode(fread($handle,filesize($sftp_fd)),true);

//set the values for the inputs on the form
$cutoff = (int)$json['cutoverTime'];
$holidays = "";
foreach ($json['holidays'] as $holiday) {
	$holidays = $holidays . $holiday .",";
}

$holidays = rtrim($holidays, ',');
?>

<html>
	<head>
		<h4>Shipping Controls</h4>
	</head>
	<form method="post">
		<p>Overnight Cutoff Time (Military Time, 1700 = 5:00 PM):</p>
		<input type="text" id="cutOffInput" name="cutOffInput" value="<?php echo (isset($cutoff))?$cutoff:''; ?>"><br><br>
		<p>Shipping Holidays (Comma Delimited)</p>
		<div class = "holidayField"></div><input type="text" id="holidays" name="holidays" value="<?php echo (isset($holidays))?$holidays:''; ?>"></div><br><br>
		<input type="submit" name = "submit" value="submit">
	</form>
</html>



