<?php
 require "./lib/classes.php";

$logger = new Logger('log.txt');

$d = Dispatcher::getInstance();

$d->setLogger($logger);
$left_bank = new Pool();
$left_bank->push(new Father('Papa', 1))
            ->push(new Mother('Mom', 2))
            ->push(new Child('daughter Anna', 3))
            ->push(new Child('son Rob', 4))
            ->push(new Fisher('Fisher', 10));

$d->setLeftBank($left_bank);

$d->setBoat(new Boat());

$d->process();

$d->showStat();