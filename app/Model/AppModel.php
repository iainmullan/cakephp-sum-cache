<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	var $recursive = -1;
	var $actsAs = array('Containable');

	function updateCounterCache($keys = array(), $created = false) {
		parent::updateCounterCache($keys, $created);
		$this->updateSumCache($keys, $created);
	}

	 /**
	  * Updates the sumCache fields of belongsTo associations after a save or delete operation
	  * An extension Cake's built-in counterCache mechanism
	  * Based on article: http://paulherron.net/articles/view/counting_users_votes_with_a_cakephp_sum_cache
	  * Refinements and improvements by Iain Mullan
	  * NB. This code has only been tested with a MySQL datasource. Due to the use of the SUM() function (ie. DBMS-specific SQL), it's reliability with other types of datasource is not guaranteed.
	  * @return void
	  * @access public
	  */
	function updateSumCache($keys = array(), $created = false) {
		
		if (empty($keys)) {
			$keys = $this->data[$this->alias];
		}
		
		foreach ($this->belongsTo as $parent => $assoc) {
		
			if (isset($assoc['sumCache'])) {
		
				if ($assoc['sumCache'] === true) {
					$assoc['sumCache'] = Inflector::underscore($this->alias) . '_sum';
				}
		
				if ($this->{$parent}->hasField($assoc['sumCache'])) {

					if (!isset($keys[$assoc['foreignKey']])) {
						CakeLog::write('model', "ERROR: {$this->name}.{$assoc['foreignKey']} is not set - can't update {$parent}.{$assoc['sumCache']}");
						break;
					}

					CakeLog::write('model', "Calculating sumCache for {$parent}.{$assoc['sumCache']} from {$this->name}.{$assoc['sumField']} ");

					$conditions = array($this->escapeField($assoc['foreignKey']) => $keys[$assoc['foreignKey']]);
					if (isset($assoc['sumScope'])) {
						$conditions[] = $assoc['sumScope'];
					}

					if (!isset($assoc['sumField'])) {
						$assoc['sumField'] = 'amount'; // default name of field to sum
					}

					$fields = 'SUM('.$this->name.'.'.$assoc['sumField'].') AS '.$assoc['sumField'].'';
					$recursive = -1;
					list($edge) = array_values($this->find('first', compact('conditions', 'fields', 'recursive')));

					if (empty($edge[$assoc['sumField']])) {
						$sum = 0;
					} else {
						$sum = $edge[$assoc['sumField']];
					}

					$this->{$parent}->updateAll(
						array($assoc['sumCache'] => $sum),
						array($this->{$parent}->escapeField() => $keys[$assoc['foreignKey']])
					);
		
				}
			}
		}

	}

}

