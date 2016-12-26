<?php

class CreateLang extends Ruckusing_Migration_Base
{
    const TABLE = 'lang';

	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('code', 'string', ['limit' => 3, 'null' => false,]);
		$table->column('name', 'string', ['limit' => 255, 'null' => false,]);
		$table->column('active', 'tinyinteger', ['unsigned' => true, 'null' => false, 'default' => 0,]);
		$table->column('locale', 'string', ['limit' => 5, 'null' => false,]);
		$table->finish();
		
		$this->execute("INSERT INTO `".self::TABLE."` (`id`, `code`, `name`, `locale`) VALUES".
						"(1, 'en', 'English', 'en_US'),".
						"(2, 'ru', 'Russian', 'ru_RU');"
		);
	}//up()

    public function down()
    {
    	$this->drop_table(self::TABLE);
    }//down()
}
