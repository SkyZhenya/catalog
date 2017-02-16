<?php

class ModifyProductAddFlag extends Ruckusing_Migration_Base
{
    public function up()
    {
		$query = <<<'QUERY'
		ALTER TABLE `product`
			ADD flag ENUM("in edit","finished");
QUERY;

		$this->query($query);
    }//up()

    public function down()
    {
    }//down()
}
