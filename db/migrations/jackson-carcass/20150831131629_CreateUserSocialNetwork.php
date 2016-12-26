<?php

class CreateUserSocialNetwork extends Ruckusing_Migration_Base
{
	const TABLE = 'userSocialNetwork';

	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('userId', 'integer', ['unsigned' => true, 'null' => false,]);
		$table->column('identifierId', 'string', ['limit' => 50, 'null' => false,]);
		$table->column('provider', 'string', ['limit' => 255, 'null' => false,]);
		$table->finish();

		$this->add_index(self::TABLE, ['identifierId', 'provider'], ['unique' => true,]);
		
		$query = "ALTER TABLE `".self::TABLE."`
			ADD CONSTRAINT `FK_user_socialnetwork_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;";
		$this->query($query);
	}//up()

	public function down()
	{
		$this->drop_table(self::TABLE);
	}//down()
}
