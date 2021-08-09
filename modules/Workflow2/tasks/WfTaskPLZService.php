<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskPLZService	 extends \Workflow\Task
{
    protected $_internalConfiguration = true;
    protected $_configFields = array(
        "WebService" => array(
            array(
                "key" => "username",
                "label" => "geonames.org Username<br/><a href='http://www.geonames.org/login'>http://www.geonames.org/login</a>",
                "type" => "templatefield"
            ),
        ),
        "INPUT" => array(
            array(
                "key" => "input_plz",
                "label" => "Postalcode",
                "type" => "templatefield"
            ),
            array(
                "key" => "input_country",
                "label" => "Country:<br><span style='font-size:11px;font-style:italic;'>(if available)</span>",
                "type" => "templatefield"
            )
        ),
        "OUTPUT" => array(
            array(
                "key" => "output_city",
                "label" => "City",
                "type" => "envvar"
            ),
            array(
                "key" => "output_state",
                "label" => "State",
                "type" => "envvar"
            ),
            array(
                "key" => "output_country",
                "label" => "Country",
                "type" => "envvar"
            ),
        )
    );

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        set_include_path($this->getAdditionalPath('plzservice').PATH_SEPARATOR.get_include_path());
        require_once 'Services/GeoNames.php';

        $wsUsername = $this->get("username", $context);
        $geo = new Services_GeoNames($wsUsername);

        $postalcode = $this->get("input_plz", $context);
        $country = $this->get("input_country", $context);

        if(empty($country) || strlen($country) > 2) {
            $country = "DE";
        }
        try {
            $return = $geo->postalCodeLookup(array("postalcode" => $postalcode, "country" => $country));
        } catch (\Exception $exp) { return 'yes'; }

        if(count($return) == 0) {
            $state = "";
            $country = "";
            $city = "";
        } else {
            $return = $return[0];
            $state = $return->adminName1;
            $country = $return->countryCode;
            $city = $return->placeName;
        }

        $fieldCity = $this->get("output_city");
        $fieldState = $this->get("output_state");
        $fieldCountry = $this->get("output_country");

        if(!empty($fieldCity)) $context->setEnvironment($fieldCity, $city);
        if(!empty($fieldState)) $context->setEnvironment($fieldState, $state);
        if(!empty($fieldCountry)) $context->setEnvironment($fieldCountry, $country);

		return "yes";
    }
	

}
