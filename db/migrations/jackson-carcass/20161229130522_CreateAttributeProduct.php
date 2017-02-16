<?php

class CreateAttributeProduct extends Ruckusing_Migration_Base
{
	const TABLE = 'attributeProduct';
    public function up()
    {
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('productId', 'integer', ['null' => false, 'unsigned' => true]);
		$table->column('attributeId', 'integer', ['null' => false, 'unsigned' => true]);
		$table->column('value', 'string', ['null' => false]);
		$table->finish();
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (productId) REFERENCES product(id)');
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (attributeId) REFERENCES attribute(id)');
    }//up()

    public function down()
    {
		$this->drop_table(self::TABLE);
    }//down()
}
