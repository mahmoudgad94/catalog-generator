<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductCustomFieldValue;
use App\Entity\Tag;
use App\Repository\CategoryRepository;
use App\Repository\CustomFieldDefinitionRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;

class CsvImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private TagRepository $tagRepo,
        private CategoryRepository $categoryRepo,
        private CustomFieldDefinitionRepository $cfRepo,
    ) {}

    /**
     * Parse CSV and return preview data without persisting anything.
     */
    public function preview(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $rows = [];
        $errors = [];
        $newTags = [];
        $newCategories = [];
        $rowNumber = 0;

        $existingTags = [];
        foreach ($this->tagRepo->findAll() as $tag) {
            $existingTags[strtolower($tag->getName())] = $tag;
        }

        $existingCategories = [];
        foreach ($this->categoryRepo->findAll() as $cat) {
            $existingCategories[strtolower($cat->getName())] = $cat;
        }

        foreach ($records as $record) {
            $rowNumber++;
            $rowErrors = [];

            if (empty($record['name'] ?? '')) {
                $rowErrors[] = 'Name is required';
            }

            if (isset($record['price']) && !is_numeric($record['price'])) {
                $rowErrors[] = 'Price must be numeric';
            }

            // Check tags
            if (!empty($record['tags'] ?? '')) {
                $tagNames = array_map('trim', explode('|', $record['tags']));
                foreach ($tagNames as $tagName) {
                    if ($tagName && !isset($existingTags[strtolower($tagName)])) {
                        $newTags[$tagName] = true;
                    }
                }
            }

            // Check categories
            if (!empty($record['categories'] ?? '')) {
                $catPaths = array_map('trim', explode('|', $record['categories']));
                foreach ($catPaths as $catPath) {
                    $parts = array_map('trim', explode('>', $catPath));
                    foreach ($parts as $part) {
                        if ($part && !isset($existingCategories[strtolower($part)])) {
                            $newCategories[$part] = true;
                        }
                    }
                }
            }

            $rows[] = [
                'row' => $rowNumber,
                'data' => $record,
                'errors' => $rowErrors,
            ];

            if (!empty($rowErrors)) {
                $errors[$rowNumber] = $rowErrors;
            }
        }

        return [
            'rows' => $rows,
            'totalRows' => $rowNumber,
            'errors' => $errors,
            'errorCount' => count($errors),
            'newTags' => array_keys($newTags),
            'newCategories' => array_keys($newCategories),
            'headers' => $csv->getHeader(),
        ];
    }

    /**
     * Execute the import, persisting all data.
     */
    public function execute(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $created = 0;
        $skipped = 0;
        $errors = [];
        $batchSize = 50;
        $count = 0;

        // Pre-load existing tags and categories
        $tagCache = [];
        foreach ($this->tagRepo->findAll() as $tag) {
            $tagCache[strtolower($tag->getName())] = $tag;
        }

        $categoryCache = [];
        foreach ($this->categoryRepo->findAll() as $cat) {
            $categoryCache[strtolower($cat->getName())] = $cat;
        }

        // Load custom field definitions
        $cfDefs = [];
        foreach ($this->cfRepo->findAll() as $def) {
            $cfDefs[$def->getSlug()] = $def;
        }

        $this->em->beginTransaction();

        try {
            foreach ($csv->getRecords() as $rowNumber => $record) {
                $count++;

                if (empty($record['name'] ?? '')) {
                    $skipped++;
                    $errors[$count] = ['Name is required'];
                    continue;
                }

                $product = new Product();
                $product->setName($record['name']);
                $product->setDescription($record['description'] ?? null);
                $product->setPrice($record['price'] ?? '0.00');

                // Tags
                if (!empty($record['tags'] ?? '')) {
                    $tagNames = array_map('trim', explode('|', $record['tags']));
                    foreach ($tagNames as $tagName) {
                        if (!$tagName) continue;
                        $key = strtolower($tagName);
                        if (!isset($tagCache[$key])) {
                            $tag = new Tag();
                            $tag->setName($tagName);
                            $this->em->persist($tag);
                            $tagCache[$key] = $tag;
                        }
                        $product->addTag($tagCache[$key]);
                    }
                }

                // Categories
                if (!empty($record['categories'] ?? '')) {
                    $catPaths = array_map('trim', explode('|', $record['categories']));
                    foreach ($catPaths as $catPath) {
                        $parts = array_map('trim', explode('>', $catPath));
                        $parent = null;
                        $lastCat = null;

                        foreach ($parts as $part) {
                            if (!$part) continue;
                            $key = strtolower($part);
                            if (!isset($categoryCache[$key])) {
                                $cat = new Category();
                                $cat->setName($part);
                                $cat->setParent($parent);
                                $this->em->persist($cat);
                                $categoryCache[$key] = $cat;
                            }
                            $lastCat = $categoryCache[$key];
                            $parent = $lastCat;
                        }

                        if ($lastCat) {
                            $product->addCategory($lastCat);
                        }
                    }
                }

                // Custom fields
                foreach ($record as $header => $value) {
                    if (str_starts_with($header, 'custom_field_')) {
                        $slug = substr($header, strlen('custom_field_'));
                        if (isset($cfDefs[$slug]) && $value !== '' && $value !== null) {
                            $cfv = new ProductCustomFieldValue();
                            $cfv->setDefinition($cfDefs[$slug]);
                            $cfv->setValue($value);
                            $product->addCustomFieldValue($cfv);
                        }
                    }
                }

                $this->em->persist($product);
                $created++;

                if ($count % $batchSize === 0) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'total' => $count,
        ];
    }
}
