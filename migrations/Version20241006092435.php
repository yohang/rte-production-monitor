<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241006092435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds latitude and longitude to production_unit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE production_unit ADD latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE production_unit ADD longitude DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE production_unit DROP latitude');
        $this->addSql('ALTER TABLE production_unit DROP longitude');
    }
}
