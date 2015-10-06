<?php

class ModifyUserAutologinAddForeignKeys extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$query = <<<'QUERY'
		ALTER TABLE `userAutologin`
			ADD CONSTRAINT `FK_user_autologin_user` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
QUERY;

		$this->query($query);
    }//up()

    public function down()
    {
    }//down()
}
