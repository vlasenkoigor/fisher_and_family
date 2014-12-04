<?php
 require "./lib/classes.php";

$logger = new Logger('log.txt');

$d = Dispatcher::getInstance();

$d->setLogger($logger);
$left_bank = new Pool();
$left_bank->push(new Father('Papa', 1))
            ->push(new Mother('Mom', 2))
            ->push(new Mother('Mom2', 5))
            ->push(new Child('daughter Anna', 3))
            ->push(new Child('son Rob', 4))
            ->push(new Child('son Rob1', 6))
            ->push(new Child('son Rob2', 7))
            ->push(new Child('son Rob3', 8))
            ->push(new Fisher('Fisher', 10));

$d->setLeftBank($left_bank);

$d->setBoat(new Boat());

$d->process();

$d->showStat();