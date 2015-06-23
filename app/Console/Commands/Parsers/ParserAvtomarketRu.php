<?php

namespace App\Console\Commands\Parsers;

use App\Models\ModelBrand;
use App\Models\ModelBrandModel;
use App\Models\ModelModifications;
use App\Models\ModelProperties;
use App\Models\ModelPropertiesNames;
use App\Models\ModelPropertiesTypes;
use Yangqi\Htmldom\Htmldom;

class ParserAvtomarketRu extends BaseParser
{
    public function __construct()
    {
        $this->domainURL = 'http://avtomarket.ru';
        $this->catalogURL = $this->domainURL . '/catalog';
        $this->pageEncoding = $this->getEncoding($this->domainURL);
    }

    /**
     * Start parsing.
     */
    public function parse()
    {
        $domainAnchor = $this->domainURL;
        $carBrandAnchor = $this->catalogURL;
        $carBrandDOM = new Htmldom($carBrandAnchor);

        // Find all links for subcatalogs.
        $brandsURI = $carBrandDOM->find('.cont .form a');
        $brandSize = count($brandsURI);

        for ($i = 0; $i < $brandSize; $i++) {
            $brand = $brandsURI[$i];
            $subcatalogAnchor = $domainAnchor . $brand->href;
            $subcatalogDOM = $this->generateNeedfulHtmldom($subcatalogAnchor);
            $brandName = $this->getUnitName($subcatalogDOM);

            $modelBrands = new ModelBrand();

            if ($modelBrands->getStatus($brandName) === 'parsed') {
                $this->info($brandName . ' skipped');
                continue;
            }

            $this->info('Brand: ' . $brandName);
            $modelBrands->insert($brandName, 'expected');
            $brandID = $modelBrands->getID($brandName);

            $subcatalogsURI = $subcatalogDOM->find('.cont .form a');
            $subcatalogsSize = count($subcatalogsURI);

            for ($j = 0; $j < $subcatalogsSize; $j++) {
                $model = $subcatalogsURI[$j];
                $this->info('Model URI: ' . $model->href);

                // Because of different errors: indefinetily redirect, 404, etc.;
                $toSkip = [];
                $toSkip[] = '/catalog/Toyota/BB/';

                if (in_array($model->href, $toSkip, true)) {
                    $this->info('Excluding model: ' . $model->href);
                    continue;
                }

                $modelAnchor = $domainAnchor . $model->href;
                $modelCatalogDOM = $this->generateNeedfulHtmldom($modelAnchor);
                $modelName = $this->getUnitName($modelCatalogDOM);
                $this->info('Brand model name: ' . $modelName);
                $modelBrandModels = new ModelBrandModel();

                if ($modelBrandModels->getStatus($modelName) === 'parsed') {
                    $this->info($modelName . ' skipped');
                    continue;
                }

                $modelBrandModels->insert($brandID, $modelName, 'expected');
                $brandModelID = $modelBrandModels->getID($brandID, $modelName);

                $modelsCatalogURI = $modelCatalogDOM->find('.cont strong a');
                $modelsCatalogSize = count($modelsCatalogURI);

                for ($k = 0; $k < $modelsCatalogSize; $k++) {
                    $subModel = $modelsCatalogURI[$k];
                    $modificationAnchor = $domainAnchor . $subModel->href;
                    $modificationDOM = $this->generateNeedfulHtmldom($modificationAnchor);
                    $submodelName = $this->getUnitName($modificationDOM);
                    $this->info('Submodel name: ' . $submodelName);
                    $foundModificationDOM = $modificationDOM->find('.cont .form strong a');

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

    /**
     * Get name of the current unit: brand, model, modification, etc.
     *
     * @param Htmldom $dom : DOM for parse.
     * @return string : Name of current unit.
     */
    protected function getUnitName(Htmldom $dom)
    {
        $name = trim($dom->find('h1', 0)->plaintext);
        $name = $this->toUTF8($name);
        $name = str_replace('Характеристики ', '', $name);
        $name = str_replace('&ndash;', '–', $name);
        return $name;
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
        $modificationInfoAnchor = $domainAnchor . $modification->href;
        $modificationInfoDOM = $this->generateNeedfulHtmldom($modificationInfoAnchor);
        $modificationName = trim($modificationInfoDOM->find('h1', 0)->plaintext);
        $modificationName = $this->toUTF8($modificationName);
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

        $types = $modificationInfoDOM->find('.cont .form h4');
        $typeNames = $modificationInfoDOM->find('.cont .form table');

        $sizeTypes = count($types);
        $sizeTypeNames = count($typeNames);

        if ($sizeTypes && $sizeTypes === $sizeTypeNames) {
            $data = [];

            for ($i = 0; $i < $sizeTypeNames; $i++) {
                $modificationType = $this->toUTF8($types[$i]->plaintext);
                $modelPropertiesTypes->insert($modificationType);
                $tableDOM = new Htmldom($typeNames[$i]->innertext);
                $foundInTable = $tableDOM->find('td');
                $tableSize = count($foundInTable);
                $modificationID = $modelModifications->getID($modificationInfoAnchor);

                for ($j = 0; $j < $tableSize; $j += 2) {
                    $key = trim($foundInTable[$j]->plaintext);
                    $key = $this->toUTF8($key);
                    $key = str_replace(':', '', $key);

                    $typeID = $modelPropertiesTypes->getID($modificationType);
                    $modelPropertiesNames->insert($key, $typeID);
                    $propertyNameID = $modelPropertiesNames->getID($key);

                    $value = trim($foundInTable[$j + 1]->plaintext);
                    $value = $this->toUTF8($value);

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