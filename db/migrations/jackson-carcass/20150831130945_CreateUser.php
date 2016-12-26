<?php

class CreateUser extends Ruckusing_Migration_Base
{
	const TABLE = 'user';
	
	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('name', 'string', ['limit' => 255, 'null' => false,]);
		$table->column('email', 'string', ['limit' => 255, 'null' => true, 'default' => null,]);
		$table->column('created', 'integer', ['unsigned' => true, 'null' => false]);
		$table->column('level', 'enum', ['null' => false, 'default' => 'user', 'values' => [
			'manager',
			'admin',
			'user',
		]]);
		$table->column('active', 'tinyinteger', ['unsigned' => true, 'null' => false, 'default' => 1,]);
		$table->column('newpass', 'string', ['limit' => 64, 'null' => true, 'default' => null,]);
		$table->column('code', 'string', ['limit' => 64, 'null' => false,]);
		$table->column('country', 'integer', ['unsigned' => true, 'null' => true, 'default' => 0,]);
		$table->column('login', 'string', ['limit' => 45, 'null' => true, 'default' => null,]);
		$table->column('password', 'string', ['limit' => 64, 'null' => false,]);
		$table->column('phone', 'string', ['limit' => 50, 'null' => false,]);
		$table->column('birthdate', 'date', ['null' => true, 'default' => null,]);
		$table->finish();
	}//up()

	public function down()
	{
		$this->drop_table(self::TABLE);
	}//down()
}
