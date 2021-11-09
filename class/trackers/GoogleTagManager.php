<?php

declare(strict_types=1);

/**
 * MIT License
 * Copyright (c) 2021 Electronic Student Services @ Appalachian State University
 *
 * See LICENSE file in root directory for copyright and distribution permissions.
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 * @license https://opensource.org/licenses/MIT
 */
class GoogleTagManager extends Tracker
{

    public $tagAccount;

    public function save()
    {
        $result = parent::save();
        if (PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tag_google');
        $db->addWhere('id', $this->id);

        $result = $db->select();
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        }

        $db = new PHPWS_DB('analytics_tag_google');
        $db->addValue('id', $this->id);
        $db->addValue('tagAccount', $this->tagAccount);
        if (count($result) < 1) {
            $result = $db->insert(false);
        } else {
            $result = $db->update();
        }
        if (PHPWS_Error::logIfError($result))
            return $result;
    }

    public function track()
    {
        $vars = array();

        $vars['TRACKER_ID'] = $this->tagAccount;
        $head = PHPWS_Template::process($vars, 'analytics', 'GoogleTagManager/head.tpl');
        $body = PHPWS_Template::process($vars, 'analytics', 'GoogleTagManager/body.tpl');
        self::addHead($head);
        self::addStartBody($body);
    }

    public function trackerType()
    {
        return 'GoogleTagManager';
    }

    public function processForm(array $values)
    {
        parent::processForm($values);
        $this->tagAccount = PHPWS_Text::parseInput($values['tagAccount']);
    }

    public function addForm(PHPWS_Form &$form)
    {
        $form->addText('tagAccount', $this->tagAccount);
        $form->setLabel('tagAccount', 'Account Identifier (ie, GTM-XXXXXX)');
        $form->setRequired('tagAccount');
    }

    public function joinDb(PHPWS_DB &$db)
    {
        $db->addJoin('left outer',
            'analytics_tracker', 'analytics_tag_google', 'id', 'id');
        $db->addColumn('analytics_tag_google.tagAccount');
    }

    public function getFormTemplate()
    {
        return 'GoogleTagManager/admin.tpl';
    }

}
