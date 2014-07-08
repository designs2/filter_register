<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FilterRegister
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */


/**
 * Filter "register" for FE-filtering, based on filters by the MetaModels team.
 *
 * @package    MetaModels
 * @subpackage FilterRegister
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 */
class MetaModelFilterSettingRegister extends MetaModelFilterSettingSimpleLookup
{
	/**
	 * Overrides the parent implementation to always return true, as this setting is always available for FE filtering.
	 *
	 * @return bool true as this setting is always available.
	 */
	public function enableFEFilterWidget()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		return in_array($strKeyOption, (array)$arrWidget['value']) ? true : false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		$arrCurrent = (array)$arrWidget['value'];
		// toggle if active.
		if ($this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption))
		{
			$arrCurrent = array_diff($arrCurrent, array($strKeyOption));
		}
		else
		{
			$arrCurrent[] = $strKeyOption;
		}
		return implode(',', $arrCurrent);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getParameterFilterOptions($objAttribute, $arrIds, &$arrCount = null)
	{
		$arrOptions = $objAttribute->getFilterOptions(
			$this->get('onlypossible') ? $arrIds : null,
			(bool)$this->get('onlyused'),
			$arrCount
		);

		// Remove empty values.
		foreach ($arrOptions as $mixOptionKey => $mixOptions)
		{
			// Remove html/php tags.
			$mixOptions = strip_tags($mixOptions);
			$mixOptions = trim($mixOptions);

			if ($mixOptions === '' || $mixOptions === null)
			{
				unset($arrOptions[$mixOptionKey]);
			}
		}

		$arrNewOptions = array();
		$arrNewCount   = array();

		// Sort the values, first char uppercase.
		foreach ($arrOptions as $strOptionsKey => $strOptionValue)
		{
			if ($strOptionsKey == '-')
			{
				continue;
			}

			$strFirstChar   = mb_substr($strOptionValue, 0, 1);
			$charUpperFist  = ucfirst($strFirstChar);
			$charLowerFirst = lcfirst($strFirstChar);

			$arrNewOptions[$charLowerFirst] = $charUpperFist;
			$arrNewCount[$charLowerFirst]   = $arrNewCount[$charLowerFirst] + $arrCount[$strOptionsKey];
		}

		$arrOptions = $arrNewOptions;
		$arrCount   = $arrNewCount;

		return $arrOptions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objMetaModel  = $this->getMetaModel();
		$objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
		$strParamName  = $this->getParamName();
		$strParamValue = $arrFilterUrl[$strParamName];
		$strWhat       = $strParamValue . '%';

		if ($objAttribute && $strParamName && $strParamValue)
		{
			$arrIds = $objAttribute->searchFor($strWhat);
			$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($arrIds));
			return;
		}

		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList(null));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, MetaModelFrontendFilterOptions $objFrontendFilterOptions)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

		$arrCount   = array();
		$arrOptions = $this->getParameterFilterOptions($objAttribute, $arrIds, $arrCount);

		$strParamName = $this->getParamName();
		// if we have a value, we have to explode it by comma to have a valid value which the active checks may cope with.
		if (array_key_exists($strParamName, $arrFilterUrl) && !empty($arrFilterUrl[$strParamName]))
		{
			if (is_array($arrFilterUrl[$strParamName]))
			{
				$arrParamValue = $arrFilterUrl[$strParamName];
			}
			else
			{
				$arrParamValue = explode(',', $arrFilterUrl[$strParamName]);
			}

			// ok, this is rather hacky here. The magic value of '--none--' means clear in the widget.
			if (in_array('--none--', $arrParamValue))
			{
				$arrParamValue = null;
			}
		}

		$GLOBALS['MM_FILTER_PARAMS'][] = $strParamName;

		return array(
			$this->getParamName() => $this->prepareFrontendFilterWidget(array
			(
				'label'     => array(
					// TODO: make this multilingual.
					($this->get('label') ? $this->get('label') : $objAttribute->getName()),
					'GET: ' . $strParamName
				),
				'inputType' => 'tags',
				'options'   => $arrOptions,
				'count'     => $arrCount,
				'showCount' => $objFrontendFilterOptions->isShowCountValues(),
				'eval'      => array(
					'includeBlankOption' => ($this->get('blankoption') && !$objFrontendFilterOptions->isHideClearFilter() ? true : false),
					'blankOptionLabel'   => &$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
					'multiple'           => true,
					'colname'            => $objAttribute->getColname(),
					'urlparam'           => $strParamName,
					'onlypossible'       => $this->get('onlypossible'),
					'shownumbers'        => $this->get('shownumbers'),
					'hideempty'          => $this->get('hideempty'),
					'template'           => $this->get('template')
				),
				// we need to implode again to have it transported correctly in the frontend filter.
				'urlvalue'  => !empty($arrParamValue) ? implode(',', $arrParamValue) : ''
			),
			array(),
			$arrJumpTo,
			$objFrontendFilterOptions)
		);
	}
}