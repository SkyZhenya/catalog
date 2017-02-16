<?php

class CreateList extends Ruckusing_Migration_Base
{
	const TABLE = 'list';
    public function up()
    {
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('userId', 'integer', ['null' => false, 'unsigned' => true]);
		$table->column('name', 'string',['limit' => 30, 'null' => false]);
		$table->finish();
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (userId) REFERENCES user(id)');
    }//up()

    public function down()
    {
		$this->drop_table(self::TABLE);
    }//down()
}
