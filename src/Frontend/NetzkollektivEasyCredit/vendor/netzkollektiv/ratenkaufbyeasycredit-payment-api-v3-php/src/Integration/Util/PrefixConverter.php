<?php
namespace Teambank\RatenkaufByEasyCreditApiV3\Integration\Util;

use Teambank\RatenkaufByEasyCreditApiV3\Model\Customer;

class PrefixConverter {

	protected $malePatterns = array('Herr','Mr','male','männlich');

    protected $femalePatterns = array('Frau','Ms','Miss','Mrs','female','weiblich');

    protected $diversePatterns = array('divers');

    protected $nonePatterns = array('kein','no');

	public function convert($prefix) {
		foreach ([
			Customer::GENDER_MR => $this->malePatterns,
			Customer::GENDER_MRS => $this->femalePatterns,
            Customer::GENDER_DIVERS => $this->diversePatterns,
            Customer::GENDER_NO_GENDER => $this->nonePatterns	
        ] as $gender => $patterns) {
			foreach ($patterns as $pattern) {
				if (stripos(trim($prefix), $pattern) !== false) {
					return $gender;
            	}
			}
        }
		return null;
    }
}
