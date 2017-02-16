<?php

class AddTypeFildAttribute extends Ruckusing_Migration_Base
{
    public function up()
    {
		$this->add_column('attribute', 'type', 'enum', ['null' => false, 'default' => 'string', 'values' => [
			'string',
			'int',
			'text',
			'float',
		]]);
    }//up()

    public function down()
    {
		$this->remove_column('attribute', 'type');
    }//down()
}
