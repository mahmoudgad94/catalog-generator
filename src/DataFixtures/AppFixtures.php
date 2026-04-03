<?php

namespace App\DataFixtures;

use App\Entity\Catalog;
use App\Entity\CatalogAccess;
use App\Entity\CatalogAccessPassword;
use App\Entity\CatalogFilter;
use App\Entity\CatalogInputConfig;
use App\Entity\CatalogSort;
use App\Entity\Category;
use App\Entity\CustomFieldDefinition;
use App\Entity\Product;
use App\Entity\ProductCustomFieldValue;
use App\Entity\Tag;
use App\Enum\AccessMode;
use App\Enum\CustomFieldType;
use App\Enum\FilterOperator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CatalogTemplateFixture::class];
    }

    public function load(ObjectManager $manager): void
    {
        // --- Categories ---
        $electronics = $this->createCategory($manager, 'Electronics', null, 0);
        $phones = $this->createCategory($manager, 'Phones', $electronics, 0);
        $laptops = $this->createCategory($manager, 'Laptops', $electronics, 1);
        $accessories = $this->createCategory($manager, 'Accessories', $electronics, 2);

        $clothing = $this->createCategory($manager, 'Clothing', null, 1);
        $mens = $this->createCategory($manager, 'Men', $clothing, 0);
        $womens = $this->createCategory($manager, 'Women', $clothing, 1);

        $home = $this->createCategory($manager, 'Home & Garden', null, 2);
        $furniture = $this->createCategory($manager, 'Furniture', $home, 0);
        $decor = $this->createCategory($manager, 'Decor', $home, 1);

        // --- Tags ---
        $tagNew = $this->createTag($manager, 'New');
        $tagSale = $this->createTag($manager, 'Sale');
        $tagBestseller = $this->createTag($manager, 'Bestseller');
        $tagPremium = $this->createTag($manager, 'Premium');
        $tagEco = $this->createTag($manager, 'Eco-Friendly');
        $tagLimited = $this->createTag($manager, 'Limited Edition');

        // --- Custom Fields ---
        $cfColor = new CustomFieldDefinition();
        $cfColor->setName('Color');
        $cfColor->setSlug('color');
        $cfColor->setFieldType(CustomFieldType::Select);
        $cfColor->setOptions(['Red', 'Blue', 'Green', 'Black', 'White', 'Silver', 'Gold']);
        $cfColor->setPosition(0);
        $manager->persist($cfColor);

        $cfWeight = new CustomFieldDefinition();
        $cfWeight->setName('Weight');
        $cfWeight->setSlug('weight');
        $cfWeight->setFieldType(CustomFieldType::Text);
        $cfWeight->setPosition(1);
        $manager->persist($cfWeight);

        $cfInStock = new CustomFieldDefinition();
        $cfInStock->setName('In Stock');
        $cfInStock->setSlug('in_stock');
        $cfInStock->setFieldType(CustomFieldType::Boolean);
        $cfInStock->setPosition(2);
        $manager->persist($cfInStock);

        $cfReleaseDate = new CustomFieldDefinition();
        $cfReleaseDate->setName('Release Date');
        $cfReleaseDate->setSlug('release_date');
        $cfReleaseDate->setFieldType(CustomFieldType::Date);
        $cfReleaseDate->setPosition(3);
        $manager->persist($cfReleaseDate);

        $cfSku = new CustomFieldDefinition();
        $cfSku->setName('SKU');
        $cfSku->setSlug('sku');
        $cfSku->setFieldType(CustomFieldType::Text);
        $cfSku->setPosition(4);
        $manager->persist($cfSku);

        $manager->flush();

        // --- Products ---
        $products = [
            ['name' => 'iPhone 15 Pro', 'desc' => 'Latest Apple smartphone with A17 Pro chip, titanium design, and advanced camera system.', 'price' => '999.99', 'cats' => [$phones], 'tags' => [$tagNew, $tagPremium], 'cf' => ['color' => 'Black', 'weight' => '187g', 'in_stock' => '1', 'sku' => 'APPL-IP15P']],
            ['name' => 'Samsung Galaxy S24', 'desc' => 'Flagship Android phone with AI features, stunning display, and all-day battery.', 'price' => '849.99', 'cats' => [$phones], 'tags' => [$tagNew, $tagBestseller], 'cf' => ['color' => 'Silver', 'weight' => '168g', 'in_stock' => '1', 'sku' => 'SAM-GS24']],
            ['name' => 'Google Pixel 8', 'desc' => 'Pure Android experience with best-in-class computational photography.', 'price' => '699.99', 'cats' => [$phones], 'tags' => [$tagBestseller], 'cf' => ['color' => 'Green', 'weight' => '187g', 'in_stock' => '1', 'sku' => 'GOOG-PX8']],
            ['name' => 'MacBook Pro 16"', 'desc' => 'Professional laptop with M3 Max chip, Liquid Retina XDR display, and 22-hour battery.', 'price' => '2499.99', 'cats' => [$laptops], 'tags' => [$tagPremium], 'cf' => ['color' => 'Silver', 'weight' => '2.14kg', 'in_stock' => '1', 'sku' => 'APPL-MBP16']],
            ['name' => 'Dell XPS 15', 'desc' => 'Thin and powerful Windows laptop with InfinityEdge display.', 'price' => '1799.99', 'cats' => [$laptops], 'tags' => [$tagBestseller], 'cf' => ['color' => 'Silver', 'weight' => '1.86kg', 'in_stock' => '1', 'sku' => 'DELL-XPS15']],
            ['name' => 'ThinkPad X1 Carbon', 'desc' => 'Business ultrabook built for professionals. Lightweight, durable, and secure.', 'price' => '1649.99', 'cats' => [$laptops], 'tags' => [$tagPremium], 'cf' => ['color' => 'Black', 'weight' => '1.12kg', 'in_stock' => '1', 'sku' => 'LEN-X1C']],
            ['name' => 'AirPods Pro 2', 'desc' => 'Premium wireless earbuds with active noise cancellation and spatial audio.', 'price' => '249.99', 'cats' => [$accessories], 'tags' => [$tagBestseller, $tagNew], 'cf' => ['color' => 'White', 'weight' => '50.8g', 'in_stock' => '1', 'sku' => 'APPL-APP2']],
            ['name' => 'USB-C Hub 7-in-1', 'desc' => 'Portable docking station with HDMI, USB-A, SD card reader, and PD charging.', 'price' => '49.99', 'cats' => [$accessories], 'tags' => [$tagSale], 'cf' => ['color' => 'Silver', 'weight' => '85g', 'in_stock' => '1', 'sku' => 'ACC-HUB7']],
            ['name' => 'Wireless Charging Pad', 'desc' => '15W fast wireless charger compatible with all Qi-enabled devices.', 'price' => '29.99', 'cats' => [$accessories], 'tags' => [$tagSale], 'cf' => ['color' => 'Black', 'weight' => '120g', 'in_stock' => '1', 'sku' => 'ACC-WCP']],
            ['name' => 'Classic Oxford Shirt', 'desc' => 'Timeless button-down oxford shirt in premium cotton.', 'price' => '79.99', 'cats' => [$mens], 'tags' => [$tagBestseller], 'cf' => ['color' => 'Blue', 'weight' => '220g', 'in_stock' => '1', 'sku' => 'CLO-OXF-M']],
            ['name' => 'Slim Fit Chinos', 'desc' => 'Comfortable stretch chinos for everyday wear.', 'price' => '59.99', 'cats' => [$mens], 'tags' => [$tagSale], 'cf' => ['color' => 'Black', 'weight' => '340g', 'in_stock' => '1', 'sku' => 'CLO-CHI-M']],
            ['name' => 'Wool Blend Blazer', 'desc' => 'Sophisticated blazer crafted from Italian wool blend fabric.', 'price' => '299.99', 'cats' => [$mens], 'tags' => [$tagPremium, $tagLimited], 'cf' => ['color' => 'Black', 'weight' => '650g', 'in_stock' => '1', 'sku' => 'CLO-BLZ-M']],
            ['name' => 'Silk Wrap Dress', 'desc' => 'Elegant wrap dress in pure silk with flattering drape.', 'price' => '189.99', 'cats' => [$womens], 'tags' => [$tagPremium], 'cf' => ['color' => 'Red', 'weight' => '180g', 'in_stock' => '1', 'sku' => 'CLO-WRP-W']],
            ['name' => 'Organic Cotton T-Shirt', 'desc' => 'Soft, sustainable crew-neck tee made from 100% organic cotton.', 'price' => '34.99', 'cats' => [$womens], 'tags' => [$tagEco, $tagBestseller], 'cf' => ['color' => 'White', 'weight' => '150g', 'in_stock' => '1', 'sku' => 'CLO-TEE-W']],
            ['name' => 'High-Rise Jeans', 'desc' => 'Classic high-rise straight leg jeans with stretch comfort.', 'price' => '89.99', 'cats' => [$womens], 'tags' => [$tagNew], 'cf' => ['color' => 'Blue', 'weight' => '450g', 'in_stock' => '1', 'sku' => 'CLO-JNS-W']],
            ['name' => 'Mid-Century Sofa', 'desc' => 'Three-seater sofa with walnut legs and premium upholstery.', 'price' => '1299.99', 'cats' => [$furniture], 'tags' => [$tagPremium], 'cf' => ['color' => 'Green', 'weight' => '45kg', 'in_stock' => '1', 'sku' => 'FRN-SOFA']],
            ['name' => 'Standing Desk', 'desc' => 'Electric height-adjustable desk with memory presets and cable management.', 'price' => '599.99', 'cats' => [$furniture], 'tags' => [$tagBestseller], 'cf' => ['color' => 'White', 'weight' => '32kg', 'in_stock' => '1', 'sku' => 'FRN-DESK']],
            ['name' => 'Bookshelf Tower', 'desc' => 'Five-tier open bookshelf in solid oak with industrial metal frame.', 'price' => '349.99', 'cats' => [$furniture], 'tags' => [$tagNew], 'cf' => ['color' => 'Black', 'weight' => '18kg', 'in_stock' => '1', 'sku' => 'FRN-BKSH']],
            ['name' => 'Ceramic Vase Set', 'desc' => 'Set of 3 handcrafted ceramic vases in matte finish.', 'price' => '69.99', 'cats' => [$decor], 'tags' => [$tagEco], 'cf' => ['color' => 'White', 'weight' => '1.2kg', 'in_stock' => '1', 'sku' => 'DEC-VASE']],
            ['name' => 'Wall Art Canvas', 'desc' => 'Abstract minimalist canvas print, gallery wrapped, ready to hang.', 'price' => '129.99', 'cats' => [$decor], 'tags' => [$tagLimited], 'cf' => ['color' => 'Gold', 'weight' => '2.5kg', 'in_stock' => '1', 'sku' => 'DEC-ART']],
        ];

        $cfMap = [
            'color' => $cfColor,
            'weight' => $cfWeight,
            'in_stock' => $cfInStock,
            'release_date' => $cfReleaseDate,
            'sku' => $cfSku,
        ];

        foreach ($products as $i => $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['desc']);
            $product->setPrice($data['price']);
            $product->setPosition($i);

            foreach ($data['cats'] as $cat) {
                $product->addCategory($cat);
            }
            foreach ($data['tags'] as $tag) {
                $product->addTag($tag);
            }
            foreach ($data['cf'] as $slug => $value) {
                if (isset($cfMap[$slug])) {
                    $cfv = new ProductCustomFieldValue();
                    $cfv->setDefinition($cfMap[$slug]);
                    $cfv->setValue($value);
                    $product->addCustomFieldValue($cfv);
                }
            }

            $manager->persist($product);
        }

        $manager->flush();

        // --- Catalogs ---

        // 1. Public catalog (all products, grid)
        $gridTemplate = $manager->getRepository(\App\Entity\CatalogTemplate::class)->findOneBy(['directory' => 'grid']);
        $tableTemplate = $manager->getRepository(\App\Entity\CatalogTemplate::class)->findOneBy(['directory' => 'table']);
        $minimalTemplate = $manager->getRepository(\App\Entity\CatalogTemplate::class)->findOneBy(['directory' => 'minimal']);

        $catalog1 = new Catalog();
        $catalog1->setName('Full Product Catalog');
        $catalog1->setDescription('Browse our complete collection of products.');
        $catalog1->setTemplate($gridTemplate);
        $catalog1->setIsPublished(true);

        $access1 = new CatalogAccess();
        $access1->setMode(AccessMode::Public);
        $catalog1->setAccess($access1);

        // Add input configs
        $this->addInput($catalog1, 'search', 'Search', 0);
        $this->addInput($catalog1, 'category_select', 'Category', 1);
        $this->addInput($catalog1, 'tag_select', 'Tag', 2);
        $this->addInput($catalog1, 'price_range', 'Price', 3);
        $this->addInput($catalog1, 'sort_dropdown', 'Sort', 4);

        $sort1 = new CatalogSort();
        $sort1->setField('name');
        $sort1->setDirection('asc');
        $sort1->setPosition(0);
        $catalog1->addSort($sort1);

        $manager->persist($catalog1);

        // 2. Electronics catalog (password-protected, table)
        $catalog2 = new Catalog();
        $catalog2->setName('Electronics Catalog');
        $catalog2->setDescription('Our curated selection of electronics and gadgets.');
        $catalog2->setTemplate($tableTemplate);
        $catalog2->setIsPublished(true);

        $access2 = new CatalogAccess();
        $access2->setMode(AccessMode::Password);
        $pwd = new CatalogAccessPassword();
        $pwd->setPasswordHash(password_hash('demo123', PASSWORD_DEFAULT));
        $pwd->setLabel('Demo password');
        $access2->addPassword($pwd);
        $catalog2->setAccess($access2);

        $filter2 = new CatalogFilter();
        $filter2->setField('category');
        $filter2->setOperator(FilterOperator::Eq);
        $filter2->setValue([(string) $electronics->getId()]);
        $filter2->setPosition(0);
        $catalog2->addFilter($filter2);

        $this->addInput($catalog2, 'search', 'Search', 0);
        $this->addInput($catalog2, 'sort_dropdown', 'Sort', 1);

        $manager->persist($catalog2);

        // 3. Clothing catalog (email-gated, minimal)
        $catalog3 = new Catalog();
        $catalog3->setName('Fashion Collection');
        $catalog3->setDescription('Discover our latest fashion picks.');
        $catalog3->setTemplate($minimalTemplate);
        $catalog3->setIsPublished(true);

        $access3 = new CatalogAccess();
        $access3->setMode(AccessMode::Email);
        $catalog3->setAccess($access3);

        $filter3 = new CatalogFilter();
        $filter3->setField('category');
        $filter3->setOperator(FilterOperator::Eq);
        $filter3->setValue([(string) $clothing->getId()]);
        $filter3->setPosition(0);
        $catalog3->addFilter($filter3);

        $this->addInput($catalog3, 'search', 'Search', 0);
        $this->addInput($catalog3, 'tag_select', 'Tag', 1);
        $this->addInput($catalog3, 'price_range', 'Price', 2);

        $manager->persist($catalog3);

        $manager->flush();
    }

    private function createCategory(ObjectManager $manager, string $name, ?Category $parent, int $position): Category
    {
        $cat = new Category();
        $cat->setName($name);
        $cat->setParent($parent);
        $cat->setPosition($position);
        $manager->persist($cat);
        return $cat;
    }

    private function createTag(ObjectManager $manager, string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);
        $manager->persist($tag);
        return $tag;
    }

    private function addInput(Catalog $catalog, string $type, string $label, int $position): void
    {
        $input = new CatalogInputConfig();
        $input->setInputType($type);
        $input->setLabel($label);
        $input->setPosition($position);
        $catalog->addInputConfig($input);
    }
}
