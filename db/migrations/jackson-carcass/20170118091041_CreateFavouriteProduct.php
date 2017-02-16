<?php

class CreateFavouriteProduct extends Ruckusing_Migration_Base
{
	const TABLE = 'favouriteProduct';
    public function up()
    {
		$table = $this->create_table(self::TABLE, ['id' => false]);
		$table->column('id', 'integer', ['primary_key' => true, 'unsigned' => true, 'null' => false, 'auto_increment' => true]);
		$table->column('userId', 'integer', ['null' => false, 'unsigned' => true]);
		$table->column('productId', 'integer', ['null' => false, 'unsigned' => true]);
		$table->finish();
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (userId) REFERENCES user(id)');
		$this->execute('ALTER TABLE ' . self::TABLE . ' ADD FOREIGN KEY (productId) REFERENCES product(id)');
    }//up()

    public function down()
    {
		$this->drop_table(self::TABLE);
    }//down()
}
