<?php
/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
/**
 * Class SummitAppFeedbackForm
 */
final class SummitAppFeedbackForm extends BootstrapForm {

	function __construct($controller, $name, $use_actions = true) {

        $RatingField = new TextField('rating','');
        $RatingField->setValue(0);
        $CommentField = new HtmlEditorField('comment','Comment');
        $CommentField->setRows(8);


        $fields = new FieldList (
            $RatingField,
            $CommentField
        );

		// Create action
		$actions = new FieldList();
		if($use_actions)
			$actions->push(new FormAction('saveFeedback', 'Submit'));

		$this->addExtraClass('review-form');
		parent::__construct($controller, $name, $fields, $actions);
	}

	function forTemplate() {
		return $this->renderWith(array(
			$this->class,
			'Form'
		));
	}

	function submit($data, $form) {	}

    function saveFeedback() { }
}
