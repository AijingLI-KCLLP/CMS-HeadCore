<?php


namespace Core\Commands;

use Core\Database\DatabaseConnexion;
use Core\Database\Dsn;


class DropDatabase extends AbstractCommand {
    
    public function execute(): void
    {
        $db = new DatabaseConnexion();
        $dsn = new Dsn();
        $dsn->addHostToDsn()
            ->addPortToDsn()
            ->addDbnameToDsn();
        $db->setConnexion($dsn);
        $db->getConnexion()->exec("DROP DATABASE IF EXISTS {$dsn->getDbName()};");
    }

    public function undo(): void
    {
    }

    public function redo(): void
    {
    }
    
}

?>
