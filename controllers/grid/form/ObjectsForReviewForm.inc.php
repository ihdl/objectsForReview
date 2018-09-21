<?php

/**
 * @file plugins/generic/objectsForReview/controllers/grid/form/ObjectsForReviewForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewForm
 * @ingroup controllers_grid_objectsForReview
 *
 * Form for adding/editing a funder
 *
 */

import('lib.pkp.classes.form.Form');

class ObjectsForReviewForm extends Form {
	/** @var int Context ID */
	var $contextId;

	/** @var int Submission ID */
	var $submissionId;

	/** @var ObjectsForReviewPlugin */
	var $plugin;

	/**
	 * Constructor
	 * @param $objectsForReviewPlugin objectsForReviewPlugin
	 * @param $contextId int Context ID
	 * @param $submissionId int Submission ID
	 * @param $funderId int (optional) Funder ID
	 */
	function __construct($objectsForReviewPlugin, $contextId, $submissionId, $reviewId = null) {
		parent::__construct($objectsForReviewPlugin->getTemplateResource('editObjectForReviewForm.tpl'));

		$this->contextId = $contextId;
		$this->submissionId = $submissionId;
		$this->reviewId = $reviewId;
		$this->plugin = $objectsForReviewPlugin;

		// Add form checks
		$this->addCheck(new FormValidator($this, 'identifierType', 'required', 'plugins.generic.objectsForReview.identifierTypeRequired'));
		$this->addCheck(new FormValidator($this, 'description', 'required', 'plugins.generic.objectsForReview.descriptionRequired'));
		$this->addCheck(new FormValidator($this, 'identifier', 'required', 'plugins.generic.objectsForReview.identifierRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));

	}

	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		$this->setData('submissionId', $this->submissionId);
		if ($this->reviewId) {

			$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');
			$objectForReview = $objectForReviewDao->getById($this->reviewId);
			
			$this->setData('identifierType', $objectForReview->getIdentifierType());
			$this->setData('description', $objectForReview->getDescription());
			$this->setData('identifier', $objectForReview->getIdentifier());

		}
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('identifierType', 'description', 'identifier'));
	}

	/**
	 * @copydoc Form::fetch
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('reviewId', $this->reviewId);
		$templateMgr->assign('submissionId', $this->submissionId);
		return parent::fetch($request);
	}

	/**
	 * Save form values into the database
	 */
	function execute() {
		$reviewId = $this->reviewId;


		$objectForReviewDao = DAORegistry::getDAO('ObjectForReviewDAO');

		if ($reviewId) {
			// Load and update an existing funder
			$objectForReview = $objectForReviewDao->getById($this->reviewId, $this->submissionId);
		} else {
			// Create a new
			$objectForReview = $objectForReviewDao->newDataObject();
			$objectForReview->setContextId($this->contextId);
			$objectForReview->setSubmissionId($this->submissionId);
		}

		$objectForReview->setIdentifier($this->getData('identifier'));
		$objectForReview->setIdentifierType($this->getData('identifierType'));
		$objectForReview->setDescription($this->getData('description'));


		if ($reviewId) {
			$objectForReview->updateObject($objectForReview);
		} else {
			$objectForReview = $objectForReviewDao->insertObject($objectForReview);
		}

	}
}

?>
