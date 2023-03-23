<?php


/*
 * 
 */
function get_view($view,$data = array()) {

	$varInitString = "<?php \n";

	foreach($data as $key => $value) {

		$currentVar = '$' . $key . ' = "' . $value . '";' . "\n";
		$varInitString .= $currentVar;
	}

	if(count($data) > 0) 
		$varInitString .= "?>\n";

	$file = "app/views/" . $view . ".php";
	$html =  file_get_contents($file);
	return $varInitString . $html;
}

?>