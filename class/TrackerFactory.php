<?php

/**
 * Tracker Factory
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
\phpws\PHPWS_Core::initModClass('analytics', 'Tracker.php');

class TrackerFactory
{

    public static function getActive()
    {
        $db = self::initDb();
        $db->addWhere('active', 1);

        // Exclude certain trackers if the user is logged in
        if (Current_User::isLogged()) {
            $db->addWhere('disable_if_logged', 0);
        }

        return self::runQuery($db);
    }

    public static function getById($id)
    {
        $db = self::initDb();
        $db->addWhere('id', $id);

        $trackers = self::runQuery($db);
        return $trackers[0];
    }

    public static function newByType($type)
    {
        \phpws\PHPWS_Core::initModClass('analytics', "trackers/$type.php");
        return new $type();
    }

    public static function getAll()
    {
        $db = self::initDb();
        return self::runQuery($db);
    }

    public static function getAvailableClasses()
    {
        $tracker_files = scandir(PHPWS_SOURCE_DIR . 'mod/analytics/class/trackers');
        $trackers = array();

        foreach ($tracker_files as $file) {
            if (substr($file, -4) != '.php')
                continue;
            $trackers[] = substr($file, 0, -4);
        }

        return $trackers;
    }

    protected static function initDb()
    {
        return new PHPWS_DB('analytics_tracker');
    }

    protected static function runQuery(\phpws\PHPWS_DB $db)
    {
        self::joinAll($db);
        $db->addColumn('analytics_tracker.*');
        try {
            $result = $db->select();
        } catch (\Exception $e) {
            if (\Current_User::isDeity()) {
                \Layout::add('<div class="alert alert-danger">Analytics is returning an error: ' . $e->getMessage() . '</div>', 'analytics');
            }
            return false;
        }
        if (PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        $trackers = array();

        foreach ($result as $tracker) {
            $found = \phpws\PHPWS_Core::initModClass('analytics', "trackers/{$tracker['type']}.php");
            if (!$found) {
                continue;
            }
            $trackerType = $tracker['type'];
            $t = new $trackerType;
            \phpws\PHPWS_Core::plugObject($t, $tracker);
            $trackers[] = $t;
        }

        return $trackers;
    }

    protected static function joinAll(PHPWS_DB &$db)
    {
        $trackers = self::getAvailableClasses();
        foreach ($trackers as $tracker) {
            $t = self::newByType($tracker);
            $t->joinDb($db);
        }
    }

}
