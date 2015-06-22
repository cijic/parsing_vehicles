<?php

namespace App\Console\Commands\Parsers;

use App\Models\ModelBrand;
use App\Models\ModelBrandModel;
use App\Models\ModelModifications;
use App\Models\ModelProperties;
use App\Models\ModelPropertiesNames;
use App\Models\ModelPropertiesTypes;
use Yangqi\Htmldom\Htmldom;

class ParserAutobanBy extends BaseParser
{
    public function __construct()
    {
        $this->domainURL = 'http://www.autoban.by/';
        $this->catalogURL = $this->domainURL . '/catalog/car';
        $this->pageEncoding = $this->getEncoding($this->domainURL);
    }

    public function parse()
    {
        $modelBrands = new ModelBrand();
        $modelBrandModels = new ModelBrandModel();

        $domainAnchor = $this->domainURL;
        $carBrandAnchor = $this->catalogURL;
        $carBrandDOM = $this->generateNeedfulHtmldom($carBrandAnchor);

        // Find all links for subcatalogs.
        $brandsURI = $carBrandDOM->find('.catalog-tabs li.catalog-tabs-item-list__item a');
        $brandSize = count($brandsURI);

        for ($i = 0; $i < $brandSize; $i++) {
            $brand = $brandsURI[$i];
            $subcatalogAnchor = $domainAnchor . $brand->href;
            $subcatalogDOM = $this->generateNeedfulHtmldom($subcatalogAnchor);
            $brandName = $subcatalogDOM->find('.model-logo__item-title-main')[0]->plaintext;

            if ($modelBrands->getStatus($brandName) === 'parsed') {
                $this->info($brandName . ' skipped');
                continue;
            }

            $this->info('Brand: ' . $brandName);
            $modelBrands->insert($brandName, 'expected');
            $brandID = $modelBrands->getID($brandName);

            $subcatalogsURI = $subcatalogDOM->find('.car-model__list li.car-model__item a');
            $subcatalogsSize = count($subcatalogsURI);

            for ($j = 0; $j < $subcatalogsSize; $j++) {
                $model = $subcatalogsURI[$j];

                $modelAnchor = $domainAnchor . $model->href;
                $modelCatalogDOM = $this->generateNeedfulHtmldom($modelAnchor);
                $modelName = $modelCatalogDOM->find('.model-logo__item-title-main')[0]->plaintext;
                $this->info('Brand model name: ' . $modelName);

                if ($modelBrandModels->getStatus($modelName) === 'parsed') {
                    $this->info($modelName . ' skipped');
                    continue;
                }

                $modelBrandModels->insert($brandID, $modelName, 'expected');
                $brandModelID = $modelBrandModels->getID($brandID, $modelName);

                $modelsCatalogURI = $modelCatalogDOM->find('h3 a');
                $modelsCatalogSize = count($modelsCatalogURI);

                for ($k = 0; $k < $modelsCatalogSize; $k++) {
                    $subModel = $modelsCatalogURI[$k];
                    $modificationAnchor = $domainAnchor . $subModel->href;
                    $modificationDOM = $this->generateNeedfulHtmldom($modificationAnchor);
                    $foundModificationDOM = $modificationDOM->find('.left a.real_link');

                    if (!count($foundModificationDOM)) {
                        $data = [];
                        $data['modification'] = $subModel;
                        $data['domainAnchor'] = $domainAnchor;
                        $data['brandModelID'] = $brandModelID;
                        $this->parseModification($data);
                    } else {
                        foreach ($foundModificationDOM as $modification) {
                            $data = [];
                            $data['modification'] = $modification;
                            $data['domainAnchor'] = $domainAnchor;
                            $data['brandModelID'] = $brandModelID;
                            $this->parseModification($data);
                        }
                    }
                }

                $modelBrandModels->updateStatus('parsed', $modelName);
                sleep(mt_rand(1, 5));
            }

            $modelBrands->updateStatus('parsed', $brandName);
            sleep(mt_rand(1, 8));
        }
    }

    protected function parseModificationDirect(
        $modification,
        $domainAnchor,
        $brandModelID
    ) {
        $modelModifications = new ModelModifications();
        $modelProperties = new ModelProperties();
        $modelPropertiesTypes = new ModelPropertiesTypes();
        $modelPropertiesNames = new ModelPropertiesNames();
//        $modificationRelativeURL = $modification->href;
        $modificationInfoAnchor = $domainAnchor . $modification->href;
        $modificationInfoDOM = $this->generateNeedfulHtmldom($modificationInfoAnchor);
        $modificationName = $modificationInfoDOM->find('h1', 0)->plaintext;

        $modificationName = str_replace('Характеристики ', '', $modificationName);
        $modificationName = str_replace('&ndash;', '–', $modificationName);
        $modificationName = str_replace('&hellip;', '…', $modificationName);

        if ($modelModifications->getStatus($modificationInfoAnchor) === 'parsed') {
            $this->info($modificationName . ' skipped.');
            return;
        }

        $this->info('Parsing model modification: ' . $modificationName . '...');

        $modelModifications->insert(
            $modificationInfoAnchor,
            $modificationName,
            'expected',
            $brandModelID);

        $types = $modificationInfoDOM->find('.oh strong');
        $typeNames = $modificationInfoDOM->find('table.char-item__table');

        $sizeTypes = count($types);
        $sizeTypeNames = count($typeNames);

        if ($sizeTypes && $sizeTypes === $sizeTypeNames) {
            $data = [];

            for ($i = 0; $i < $sizeTypeNames; $i++) {
                $modificationType = $types[$i]->plaintext;
                $modelPropertiesTypes->insert($modificationType);

                $tableDOM = new Htmldom($typeNames[$i]->innertext);
                $foundInTable = $tableDOM->find('td');
                $tableSize = count($foundInTable);
                $modificationID = $modelModifications->getID($modificationInfoAnchor);

                for ($j = 0; $j < $tableSize; $j += 2) {
                    $key = $foundInTable[$j]->plaintext;
                    $key = str_replace(':', '', $key);
                    $typeID = $modelPropertiesTypes->getID($modificationType);
                    $modelPropertiesNames->insert($key, $typeID);
                    $propertyNameID = $modelPropertiesNames->getID($key);
                    $value = $foundInTable[$j + 1]->plaintext;
                    $newRow['names_id'] = $propertyNameID;
                    $newRow['modification_id'] = $modificationID;
                    $newRow['value'] = $value;

                    $data[] = $newRow;
                }

                $foundInTable = [];

                if ($modificationType === 'Прочее') {
                    $startData = $modificationInfoDOM->find('.cont >> .form >> tbody >> tr >> td');

                    for ($k = 0; $k < 6; $k++) {
                        $foundInTable[] = $startData[$k];
                    }

                    $tableSize = count($foundInTable);
                    $modificationID = $modelModifications->getID($modificationInfoAnchor);

                    for ($j = 0; $j < $tableSize; $j += 2) {
                        $key = $this->toUTF8($foundInTable[$j]->plaintext);
                        $key = str_replace(':', '', $key);
                        $typeID = $modelPropertiesTypes->getID($modificationType);
                        $modelPropertiesNames->insert($key, $typeID);
                        $propertyNameID = $modelPropertiesNames->getID($key);
                        $value = $this->toUTF8($foundInTable[$j + 1]->plaintext);

                        $newRow['names_id'] = $propertyNameID;
                        $newRow['modification_id'] = $modificationID;
                        $newRow['value'] = $value;

                        $data[] = $newRow;
                    }

                    $modelProperties->insert($data);
                }
            }

            $modelProperties->insert($data);
        }

        $modelModifications->updateStatus('parsed', $modificationInfoAnchor);
    }
}