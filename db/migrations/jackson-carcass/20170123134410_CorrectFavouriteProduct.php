<?php

class CorrectFavouriteProduct extends Ruckusing_Migration_Base
{
	const TABLE = 'favouriteProduct';
    public function up()
    {
		$query = <<<'QUERY'
		ALTER TABLE `favouriteProduct`
			DROP COLUMN `userId`;
QUERY;
		$this->query($query);
    }//up()

    public function down()
    {
		
    }//down()
}
