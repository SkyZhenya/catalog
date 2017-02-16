<?php

class ModifyFavouriteProductAddReference extends Ruckusing_Migration_Base
{
    public function up()
    {
		$query = <<<'QUERY'
		ALTER TABLE `favouriteProduct`
			ADD CONSTRAINT `listId` FOREIGN KEY (`listId`) REFERENCES `list` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
QUERY;

		$this->query($query);
    }//up()

    public function down()
    {
    }//down()
}
