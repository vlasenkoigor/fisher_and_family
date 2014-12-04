<?php

interface iPerson {
    public function getId();
}
Class Person implements iPerson{
    public function __construct($name, $id){
        $this->setId($id);
        $this->setName($name);
    }

    private $id;

    private $name;

    private $from_family;

    private $is_baby;

    private $weight;

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getFromFamily()
    {
        return $this->from_family;
    }

    /**
     * @param mixed $from_family
     */
    public function setFromFamily($from_family)
    {
        $this->from_family = $from_family;
    }

    /**
     * @return mixed
     */
    public function getIsBaby()
    {
        return $this->is_baby;
    }

    /**
     * @param mixed $is_baby
     */
    public function setIsBaby($is_baby)
    {
        $this->is_baby = $is_baby;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}



Class Mother extends Person {

    public function __construct($name,  $id){
        parent::__construct($name, $id);
        $this->setFromFamily(true);
        $this->setIsBaby(false);
        $this->setWeight(2);
    }

}

Class Father extends Person {

    public function __construct($name,  $id){
        parent::__construct($name, $id);
        $this->setFromFamily(true);
        $this->setIsBaby(false);
        $this->setWeight(2);
    }
}

Class Child extends Person {

    public function __construct($name,  $id){
        parent::__construct($name, $id);
        $this->setFromFamily(true);
        $this->setIsBaby(true);
        $this->setWeight(1);
    }


}

Class Fisher extends Person {

    public function __construct($name,  $id){
        parent::__construct($name, $id);
        $this->setFromFamily(false);
        $this->setIsBaby(false);
        $this->setWeight(2);
    }


}


Class Logger {
    public function __construct($filename){
        $this->file = fopen($filename, 'w');
    }

    private $file;

    public function write($text){
        fwrite($this->file, date('Y-m-d h:i:s')." ".$text."\n");
    }
}

Class Pool {
    private $store = array();

    public function toString(){
        $s = "";
        foreach ($this->store as $v) {
            $s .= ( $v->getName() ) ." ";
        }

        return $s;
    }

    public function push(iPerson $i){
        $this->store[$i->getId()] = $i;
        return $this;
    }

    public function pushAll($a){
        foreach ($a as $k=>$val){
            $this->store[$val->getId()] = $val;
        }
        return $this;
    }


    private function get($id){
        return $this->store[$id];
    }

    public  function remove($id){
        unset($this->store[$id]);
        return $this;
    }
    public  function removeAll($a){
        foreach ($a as $val){
            unset($this->store[$val->getId()]);
        }
        return $this;
    }

    public function isEmpty(){
        return count($this->store) == 0 ? true : false;
    }

    public function getMore($opacity){
        $cnt = 0;
        if ($this->isEmpty()) return array();

        $a = array();
        foreach($this->store as $k => $val){
            if ( $val->getWeight() == 1 ){
                $a[] = $val;
                $cnt +=1;
                if ($cnt == $opacity) return $a;
            }
        }

        if ($cnt == $opacity) return $a;

        $a = array();
        foreach($this->store as $k => $val){
            if ( $val->getWeight() == 2 ){
                $a[] = $val;
                return $a;
            }
        }
    }
    public function getLess(){
        $cnt = 0;
        foreach($this->store as $k => $val){
            if ( $val->getWeight() == 1 ){
                $a[] = $val;
                $cnt ++;
                break;
            }
        }

        if ($cnt != 0) return $a;

        $a = array();
        foreach($this->store as $k => $val){
            if ( $val->getWeight() == 2 ){
                $a[] = $val;
                return $a;
            }
        }

    }
}

Class Boat {

    private $opacity = 2;

    private $state = 'left'; //enum : left, right


    public function crossTheRiver(){
        $this->state = $this->state =='left' ? 'right' : 'left';
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getOpacity()
    {
        return $this->opacity;
    }

}

/*
 * Dispatcher  - singleton
 * */
Class Dispatcher {

    /**
     * @var self
     */
    private static $instance;
    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    private function __construct()
    {
        $this->setRightBank(new Pool());
    }


    private function __clone()
    {
    }


    private function __sleep()
    {
    }


    private function __wakeup()
    {
    }

    /*collection of people from a left bank*/
    private $left_bank;

    /*collection of people from a right bank (empty default)*/
    private $right_bank;

    private $logger;

    private $boat;

    private $cross_counter = 0;
    /**
     * @param mixed $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param mixed $left_bank
     */
    public function setLeftBank($left_bank)
    {
        $this->left_bank = $left_bank;
    }

    /**
     * @param mixed $right_bank
     */
    public function setRightBank($right_bank)
    {
        $this->right_bank = $right_bank;
    }

    /**
     * @param mixed $boat
     */
    public function setBoat($boat)
    {
        $this->boat = $boat;
    }

    private function passagersToString($a){
        $s = "";

        foreach($a as $val) {
            $s .= $val->getName() .', ';
        }

        return $s;
    }

    public function process(){
        $this->logger->write('Lets get started');

//        $this->logger->write("left bank : ".$this->left_bank->toString());
//        $this->logger->write("right bank : ".$this->right_bank->toString());

        while ( !$this->left_bank->isEmpty() ){

            if ($this->boat->getState() == 'left'){
                $a = $this->left_bank->getMore( $this->boat->getOpacity() );

                $this->left_bank->removeAll($a);
                $this->right_bank->pushAll($a);

                $this->logger->write($this->passagersToString($a) ." has arrived to the right bank" );

            } else if ($this->boat->getState() == 'right'){
                $a = $this->right_bank->getLess( $this->boat->getOpacity() );

                $this->right_bank->removeAll($a);
                $this->left_bank->pushAll($a);

                $this->logger->write($this->passagersToString($a) ." has arrived to the left bank" );
            }
//
//            $this->logger->write("left bank : ".$this->left_bank->toString());
//            $this->logger->write("right bank : ".$this->right_bank->toString());

            $this->boat->crossTheRiver();

            $this->cross_counter++;
        }

    }

    public function showStat(){
        $this->logger->write('The river has been crossed '.$this->cross_counter.' times');
    }

}