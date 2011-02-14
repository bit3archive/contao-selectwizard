<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Select Wizard
 * Copyright (C) 2011 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 * 
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Select Wizard
 * @license    LGPL
 * @filesource
 */



/**
 * Class SelectWizard
 *
 * @copyright  InfinitySoft 2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Backend
 */
class SelectWizard extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'value':
				$this->varValue = deserialize($varValue);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}

	
	/**
	 * Generate the options html.
	 * 
	 * @param mixed $varValue
	 * @return string
	 */
	protected function generateOptions($arrItems, $varValue) {
		$options = '';
		foreach ($arrItems as $k=>$item)
		{
			if (is_string($k) && is_array($item)) {
				$options .= '<optgroup label="'.specialchars($k).'">';
				$options .= $this->generateOptions($item, $varValue);
				$options .= '</optgroup>';
			} else {
				$options .= '<option value="'.specialchars($item['value']).'"'.$this->optionSelected($item['value'], $varValue).'>'.$item['label'].'</option>';
			}
		}
		return $options;
	}

	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/selectWizard/html/selectWizard.js';
		
		$this->import('Database');

		$arrButtons = array('copy', 'up', 'down', 'delete');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}
		}

		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($this->currentRecord);

		$strField = $this->strField;
		$arrSelection = deserialize($objRow->$strField);

		// Get new value
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$this->varValue = $this->Input->post($this->strId);
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !count($this->varValue))
		{
			$this->varValue = array('');
		}

		// Save the value
		if ($this->Input->get($strCommand) || $this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			// Reload the page
			if (is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
			{
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
		}
		
		$return = '';

		// Add label and return wizard
		$return .= '<table cellspacing="0" cellpadding="0" id="ctrl_'.$this->strId.'" class="tl_selectwizard" summary="Select wizard">
  <tbody>';

		// Add input fields
		for ($i=0; $i<count($this->varValue); $i++)
		{
			// Add modules
			$strOptions = $this->generateOptions($this->options, $this->varValue[$i]['content']);
			
			$return .= '
  <tr>
    <td><select name="'.$this->strId.'['.$i.'][content]" class="tl_select" onfocus="Backend.getScrollOffset();">'.$strOptions.'</select></td>
    <td>';

			foreach ($arrButtons as $strButton)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$strButton.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$strButton]).'" onclick="Backend.selectWizard(this, \''.$strButton.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($strButton.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$strButton], 'class="tl_listwizard_img"').'</a> ';
			}

			$return .= '</td>
  </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
}

?>