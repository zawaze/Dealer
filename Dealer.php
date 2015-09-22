<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Dealer;

use Dealer\Model\DealerTabQuery;
use Thelia\Module\BaseModule;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;

/**
 * Class Dealer
 * @package Dealer
 */
class Dealer extends BaseModule
{
    const MESSAGE_DOMAIN = "dealer";
    const ROUTER = "router.dealer";

    public function postActivation(ConnectionInterface $con = null)
    {
        try {
            DealerTabQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/create.sql", __DIR__ . "/Config/insert.sql"]);
        }
    }
}
