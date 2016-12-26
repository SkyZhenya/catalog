<?php

class CreateCategory extends Ruckusing_Migration_Base
{
	const TABLE = 'category';
    public function up()
    {
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('name', 'string',['limit' => 40, 'null' => false]);
		$table->finish();
    }//up()

    public function down()
    {
		$this->drop_table(self::TABLE);
    }//down()
}
