<?php

class ModifyTemplateLocalAddForeignKeys extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$query = <<<'QUERY'
		ALTER TABLE `templatelocal`
			ADD CONSTRAINT `FK_templatelocal_template` FOREIGN KEY (`id`) REFERENCES `template` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
			ADD CONSTRAINT `FK_templatelocal_lang` FOREIGN KEY (`lang`) REFERENCES `lang` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
QUERY;

		$this->query($query);
    }//up()

    public function down()
    {
    }//down()
}
