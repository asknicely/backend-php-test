<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190620012037 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $myTable = $schema->getTable('todos');
        $myTable->addColumn('completed', 'boolean',['default' => false]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $myTable = $schema->getTable('todos');
        $myTable->dropColumn('completed');
    }
}
