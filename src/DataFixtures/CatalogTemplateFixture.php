<?php

namespace App\DataFixtures;

use App\Entity\CatalogTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CatalogTemplateFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $templates = [
            ['name' => 'Grid Layout', 'directory' => 'grid', 'thumbnail' => null],
            ['name' => 'Table Layout', 'directory' => 'table', 'thumbnail' => null],
            ['name' => 'Minimal Layout', 'directory' => 'minimal', 'thumbnail' => null],
        ];

        foreach ($templates as $data) {
            $template = new CatalogTemplate();
            $template->setName($data['name']);
            $template->setDirectory($data['directory']);
            $template->setThumbnail($data['thumbnail']);
            $manager->persist($template);
        }

        $manager->flush();
    }
}
