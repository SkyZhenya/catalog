<?php

class CreateUserAutologin extends Ruckusing_Migration_Base
{
    const TABLE = 'userAutologin';

	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('user', 'integer', ['unsigned' => true, 'null' => false,]);
		$table->column('token', 'string', ['limit' => 150, 'null' => false, 'default' => '',]);
		$table->column('expire', 'integer', ['unsigned' => true, 'null' => false, 'default' => 0, ]);
		$table->finish();
	}//up()

    public function down()
    {
    	$this->drop_table(self::TABLE);
    }//down()
}
