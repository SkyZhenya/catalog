<?php

class CreateAttribute extends Ruckusing_Migration_Base
{
	const TABLE = 'attribute';
    public function up()
    {
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('name', 'string',['limit' => 40, 'null' => false]);
		$table->column('categoryId', 'integer',['unsigned' => true, 'null' => false]);
		$table->finish();
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (categoryId) REFERENCES category(id)');
    }//up()

    public function down()
    {
		$this->drop_table(self::TABLE);
    }//down()
}
