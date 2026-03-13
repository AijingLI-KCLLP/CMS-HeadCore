<?php

namespace Core\Commands;

abstract class AbstractCommand {
    public abstract function execute(): void;
    public abstract function undo(): void;
    public abstract function redo(): void;
}


?>
