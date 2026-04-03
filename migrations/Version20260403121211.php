<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403121211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE catalog (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_published TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, template_id INT NOT NULL, UNIQUE INDEX UNIQ_1B2C3247989D9B62 (slug), INDEX IDX_1B2C32475DA0FB8 (template_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_access (id INT AUTO_INCREMENT NOT NULL, mode VARCHAR(20) NOT NULL, catalog_id INT NOT NULL, UNIQUE INDEX UNIQ_B46D331DCC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_access_log (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, accessed_at DATETIME NOT NULL, catalog_id INT NOT NULL, INDEX IDX_5A55C7CBCC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_access_password (id INT AUTO_INCREMENT NOT NULL, password_hash VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, catalog_access_id INT NOT NULL, INDEX IDX_26F58E4BB5D78D72 (catalog_access_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_filter (id INT AUTO_INCREMENT NOT NULL, field VARCHAR(255) NOT NULL, operator VARCHAR(50) NOT NULL, value JSON NOT NULL, position INT DEFAULT 0 NOT NULL, catalog_id INT NOT NULL, INDEX IDX_CDC04754CC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_input_config (id INT AUTO_INCREMENT NOT NULL, input_type VARCHAR(50) NOT NULL, label VARCHAR(255) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, options JSON DEFAULT NULL, catalog_id INT NOT NULL, INDEX IDX_29419CEFCC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_sort (id INT AUTO_INCREMENT NOT NULL, field VARCHAR(255) NOT NULL, direction VARCHAR(4) NOT NULL, position INT DEFAULT 0 NOT NULL, catalog_id INT NOT NULL, INDEX IDX_24914254CC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE catalog_template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, directory VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE custom_field_definition (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, field_type VARCHAR(50) NOT NULL, options JSON DEFAULT NULL, position INT DEFAULT 0 NOT NULL, required TINYINT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_48DC8533989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_tag (product_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_E3A6E39C4584665A (product_id), INDEX IDX_E3A6E39CBAD26311 (tag_id), PRIMARY KEY (product_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY (product_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_custom_field_value (id INT AUTO_INCREMENT NOT NULL, value LONGTEXT DEFAULT NULL, product_id INT NOT NULL, definition_id INT NOT NULL, INDEX IDX_F23AFE794584665A (product_id), INDEX IDX_F23AFE79D11EA911 (definition_id), UNIQUE INDEX unique_product_field (product_id, definition_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, product_id INT NOT NULL, INDEX IDX_64617F034584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C32475DA0FB8 FOREIGN KEY (template_id) REFERENCES catalog_template (id)');
        $this->addSql('ALTER TABLE catalog_access ADD CONSTRAINT FK_B46D331DCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_access_log ADD CONSTRAINT FK_5A55C7CBCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_access_password ADD CONSTRAINT FK_26F58E4BB5D78D72 FOREIGN KEY (catalog_access_id) REFERENCES catalog_access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_filter ADD CONSTRAINT FK_CDC04754CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_input_config ADD CONSTRAINT FK_29419CEFCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_sort ADD CONSTRAINT FK_24914254CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product_tag ADD CONSTRAINT FK_E3A6E39C4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_tag ADD CONSTRAINT FK_E3A6E39CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_custom_field_value ADD CONSTRAINT FK_F23AFE794584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_custom_field_value ADD CONSTRAINT FK_F23AFE79D11EA911 FOREIGN KEY (definition_id) REFERENCES custom_field_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C32475DA0FB8');
        $this->addSql('ALTER TABLE catalog_access DROP FOREIGN KEY FK_B46D331DCC3C66FC');
        $this->addSql('ALTER TABLE catalog_access_log DROP FOREIGN KEY FK_5A55C7CBCC3C66FC');
        $this->addSql('ALTER TABLE catalog_access_password DROP FOREIGN KEY FK_26F58E4BB5D78D72');
        $this->addSql('ALTER TABLE catalog_filter DROP FOREIGN KEY FK_CDC04754CC3C66FC');
        $this->addSql('ALTER TABLE catalog_input_config DROP FOREIGN KEY FK_29419CEFCC3C66FC');
        $this->addSql('ALTER TABLE catalog_sort DROP FOREIGN KEY FK_24914254CC3C66FC');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE product_tag DROP FOREIGN KEY FK_E3A6E39C4584665A');
        $this->addSql('ALTER TABLE product_tag DROP FOREIGN KEY FK_E3A6E39CBAD26311');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE product_custom_field_value DROP FOREIGN KEY FK_F23AFE794584665A');
        $this->addSql('ALTER TABLE product_custom_field_value DROP FOREIGN KEY FK_F23AFE79D11EA911');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('DROP TABLE catalog');
        $this->addSql('DROP TABLE catalog_access');
        $this->addSql('DROP TABLE catalog_access_log');
        $this->addSql('DROP TABLE catalog_access_password');
        $this->addSql('DROP TABLE catalog_filter');
        $this->addSql('DROP TABLE catalog_input_config');
        $this->addSql('DROP TABLE catalog_sort');
        $this->addSql('DROP TABLE catalog_template');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE custom_field_definition');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_tag');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_custom_field_value');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE tag');
    }
}
