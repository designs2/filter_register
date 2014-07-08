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
 * filter types
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['register']     = 'Register';

/**
 * fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'] = array('Remaining tags only', 'Show only options that are still assigned somewhere after the actual filter is set.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['shownumbers']  = array('Show numbers', 'Here you can choose if numbers should shown or not.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['hideempty']    = array('Hide empty values', 'Here you can choose if empty values should hide or not in the list.');