<?php

class AddUpdatedField extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$time = time();
    	$this->add_column('user', 'updated', 'integer', ['unsigned' => true, 'null' => true, 'default' => null,]);
    	$this->query("UPDATE `user` SET `updated`=".$time);
    	$this->add_column('template', 'updated', 'integer', ['unsigned' => true, 'null' => true, 'default' => null,]);
    	$this->query("UPDATE `template` SET `updated`=".$time);
    }//up()

    public function down()
    {
    	$this->remove_column('user', 'updated');
    	$this->remove_column('template', 'updated');
    }//down()
}
