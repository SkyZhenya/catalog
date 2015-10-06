<?php

class CreateTemplateLocal extends Ruckusing_Migration_Base
{
    const TABLE = 'templatelocal';

	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['unsigned' => true, 'null' => false, 'default' => 0,]);
		$table->column('lang', 'integer', ['unsigned' => true, 'null' => false, 'default' => 0,]);
		$table->column('subject', 'string', ['limit' => 255, 'null' => true, 'default' => null,]);
		$table->finish();

		$this->query('ALTER TABLE ' . self::TABLE . ' ADD COLUMN `text` LONGTEXT NULL AFTER subject');

		$this->add_index(self::TABLE, ['id', 'lang'], ['unique' => true, ]);
		
		$id = $this->query("INSERT INTO `template` (`name`) VALUES('Forgot password');");
		
		$this->query("INSERT INTO `".self::TABLE."` (`id`, `lang`, `subject`, `text`) VALUES(".$id.", 1, 'Forgot password', 'Hello, {name}!<br /><br />Looks like you (or someone) ".
		"has asked to reset your password. <br /> Now just click the link below to reset it! If you didn''t then just ignore this email and nothing will happen.<br /><br /> ".
		"<a style=\"font-weight: bold; font-size: 16px; cursor: pointer;\" href=\"{URL}auth/activeforgot/{id}/{code}\" target=\"_blank\">Reset password »</a>\r\n<p>Best regards,".
		"<br />CodeIT Carcass Administrator</p>')");
	}//up()

    public function down()
    {
    	$this->drop_table(self::TABLE);
    }//down()
}
