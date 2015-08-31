<?php

class CreateTemplate extends Ruckusing_Migration_Base
{
    const TABLE = 'template';

	public function up()
	{
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('name', 'string', ['limit' => 45, 'null' => false,]);
		$table->finish();

		$this->add_index(self::TABLE, 'name', ['unique' => true,]);
	}//up()

    public function down()
    {
    	$this->drop_table(self::TABLE);
    }//down()
}
