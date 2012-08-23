<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Michael Klapper <michael.klapper@morphodo.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class controls the visibility of available fields from be_groups records.
 *
 * @link http://www.morphodo.com/
 * @author Michael Klapper <michael.klapper@morphodo.com>
 */
class Tx_BeGroups_Service_TceMain_ProcessFieldArray {

	/**
	 * @var array
	 */
	private $setIncludeListFlag = array (
		0 => null,
		1 => true,
		2 => true,
		3 => false,
		4 => false,
		5 => false,
		6 => false,
		7 => false,
		8 => false,
	);

	/**
	 * Update inc_access_lists value if the table is "be_groups"
	 *
	 * @param array $incomingFieldArray Current record
	 * @param string $table Database table of current record
	 * @param integer $id Uid of current record
	 * @param t3lib_TCEmain  $parentObj
	 *
	 * @return string
	 */
	public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $parentObj) {
		if ($table == 'be_groups') {
			$this->resetHiddenFields($incomingFieldArray, $id);
			$this->setHideInListFlagIfTypeIsNotMeta($incomingFieldArray);
			$this->setIncludeAccessListFlag($incomingFieldArray);
			$this->mergeSubgroups($incomingFieldArray);
		}
	}

	/**
	 * Merge all updates from "subgroup_*" fields back into the original "subgroup" field.
	 *
	 * @param array $incomingFieldArray
	 * @return void
	 */
	protected function mergeSubgroups(&$incomingFieldArray) {
		$selectedList = array();
		$subgroupList = array();
		$fieldListToMerge = array('subgroup_fm', 'subgroup_pm', 'subgroup_ws', 'subgroup_r', 'subgroup_pa', 'subgroup_ts', 'subgroup_l');

		foreach ($fieldListToMerge as $fieldName) {
			if (is_array($incomingFieldArray[$fieldName])) {
				$selectedList = t3lib_div::array_merge($selectedList, array_flip($incomingFieldArray[$fieldName]));
			}
		}

			// fix expected structure
		foreach ($selectedList as $key => $value) {
			$subgroupList[] = $key;
		}

		$incomingFieldArray['subgroup'] = $subgroupList;
	}

	/**
	 * Reset all fields except the relevant for the current selected view.
	 *
	 * @param array $incomingFieldArray
	 * @param integer $id
	 * @return void
	 */
	protected function resetHiddenFields(&$incomingFieldArray, $id) {

		if (! is_null($this->setIncludeListFlag[$incomingFieldArray['tx_begroups_kind']]) ) {
			$fieldsToKeepArray = array_keys(t3lib_beFunc::getTCAtypes('be_groups', $incomingFieldArray, 1));

			foreach ($incomingFieldArray as $column => $value) {
				if (! in_array($column, $fieldsToKeepArray) && (t3lib_utility_Math::canBeInterpretedAsInteger($id) === true) ) {
					$incomingFieldArray[$column] = null;
				}
			}
		}
	}

	/**
	 * Include the access list based on the defined matrix in member
	 * Tx_BeGroups_Service_TceMain_ProcessFieldArray::$setIncludeListFlag
	 *
	 * @param array $incomingFieldArray
	 * @return void
	 */
	protected function setIncludeAccessListFlag(&$incomingFieldArray) {
			// update include access list flag
		if ($this->setIncludeListFlag[$incomingFieldArray['tx_begroups_kind']] === true) {
			$incomingFieldArray['inc_access_lists'] = 1;
		} elseif ($this->setIncludeListFlag[$incomingFieldArray['tx_begroups_kind']] === false) {
			$incomingFieldArray['inc_access_lists'] = 0;
		}
	}

	/**
	 * Be sure that the hide_in_list flag is always set to the correct
	 * value if the tx_begroups_kind is changed.
	 *
	 * @param array $incomingFieldArray
	 * @return void
	 */
	protected function setHideInListFlagIfTypeIsNotMeta(&$incomingFieldArray) {

		if ($incomingFieldArray['tx_begroups_kind'] == 3) {
			$incomingFieldArray['hide_in_lists'] = 0;
		} else {
			$incomingFieldArray['hide_in_lists'] = 1;
		}
	}
}