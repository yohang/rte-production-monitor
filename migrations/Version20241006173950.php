<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241006173950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added a foreign key to production_unit table to track productions units on the same zone';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE production_unit ADD first_unit_of_group_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN production_unit.first_unit_of_group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE production_unit ADD CONSTRAINT FK_7AB4747E4DA3043 FOREIGN KEY (first_unit_of_group_id) REFERENCES production_unit (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7AB4747E4DA3043 ON production_unit (first_unit_of_group_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE production_unit DROP CONSTRAINT FK_7AB4747E4DA3043');
        $this->addSql('DROP INDEX IDX_7AB4747E4DA3043');
        $this->addSql('ALTER TABLE production_unit DROP first_unit_of_group_id');
    }
}
