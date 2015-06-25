<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
    public function change()
    {
    }
    */

    public function up()
    {
        $users = $this->table('users');
        $users->addColumn('email',    'string', ['limit' => 64])
              ->addColumn('password', 'string', ['limit' => 64])
              ->addColumn('created',  'datetime')
              ->addColumn('updated', 'datetime', ['null' => true])
              ->addIndex(['email'], ['unique' => true])
              ->addIndex(['password'])
              ->save();
    }

    public function down()
    {
        $this->dropTable('users');
    }
}
