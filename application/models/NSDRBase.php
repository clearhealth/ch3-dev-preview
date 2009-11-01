<?php
/*****************************************************************************
*       NSDRBase.php
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

/**
 * Independent class specific for NSDR as its Base
 */

class NSDRBase {

	public function aggregateDisplay($tthis,$context,$data) {
		$ret = '';
		$values = $this->_display($data);
		$ret = implode(' ',$values);
		return $ret;
	}

	public function aggregateDisplayByLine($tthis,$context,$data) {
		$ret = '';
		$values = $this->_display($data);
		$ret = implode("\n",$values);
		return $ret;
	}

	private function _display($data) {
		$values = array();
		foreach ($data as $key=>$val) {
			if ($val instanceof ORM) {
				$val = $val->toArray();
				$tmpVal = '';
				foreach ($val as $k=>$v) {
					$tmpVal .= "$k: ".@ucwords($v) . ' ';
				}
				$values[] = $tmpVal;
			}
			else {
				$values[] = ucwords($val);
			}
		}
		return $values;
	}

}
