<?php

/**
 * MIT License
 * Copyright (c) 2021 Electronic Student Services @ Appalachian State University
 *
 * See LICENSE file in root directory for copyright and distribution permissions.
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 * @license https://opensource.org/licenses/MIT
 */
\phpws\PHPWS_Core::initModClass('analytics', 'Tracker.php');

class GoogleAnalytics4Tracker extends Tracker
{

    var $account4;

    public function save()
    {
        $result = parent::save();
        if (PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_google_4');
        $db->addWhere('id', $this->id);

        $result = $db->select();
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        }

        $db = new PHPWS_DB('analytics_tracker_google_4');
        $db->addValue('id', $this->id);
        $db->addValue('account4', $this->account4);
        if (count($result) < 1) {
            $result = $db->insert(false);
        } else {
            $result = $db->update();
        }
        if (PHPWS_Error::logIfError($result))
            return $result;
    }

    public function delete()
    {
        $result = parent::delete();
        if (PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_google_4');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PHPWS_Error::logIfError($result))
            return $result;
    }

    public function track()
    {
        $vars = array();
        $vars['TRACKER_ID'] = $this->getAccount4();
        $code = PHPWS_Template::process($vars, 'analytics', 'GoogleAnalytics4/tracker.tpl');

        self::addEndBody($code);
    }

    public function trackerType()
    {
        return 'GoogleAnalytics4Tracker';
    }

    public function addForm(PHPWS_Form &$form)
    {
        $form->addText('account4', $this->getAccount4());
        $form->setLabel('account4', dgettext('analytics', 'Account Identifier (ie, G-XXXXXXXXXX)'));
        $form->setRequired('account4');
    }

    public function processForm(array $values)
    {
        parent::processForm($values);
        $this->setAccount4(PHPWS_Text::parseInput($values['account4']));
    }

    public function joinDb(PHPWS_DB &$db)
    {
        $db->addJoin('left outer',
                'analytics_tracker', 'analytics_tracker_google_4', 'id', 'id');
        $db->addColumn('analytics_tracker_google_4.account4');
    }

    public function getFormTemplate()
    {
        return 'GoogleAnalytics4/admin.tpl';
    }

    public function setAccount4($account4)
    {
        $this->account4 = $account4;
    }

    public function getAccount4()
    {
        return $this->account4;
    }

}
