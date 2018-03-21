<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 17-10-11
 * Time: 上午11:42
 */

namespace worms\model;

use Doctrine\ORM\Mapping\Driver\XmlDriver;

class MXmlDriver extends XmlDriver
{
    protected function loadMappingFile($file)
    {
        $result     = [];
        $xmlElement = simplexml_load_string(file_get_contents($file));

        if (isset($xmlElement->entity)) {
            foreach ($xmlElement->entity as $entityElement) {
                $entityName          = (string)$entityElement['name'];
                $result[$entityName] = $entityElement;
            }
        } else if (isset($xmlElement->{'mapped-superclass'})) {
            foreach ($xmlElement->{'mapped-superclass'} as $mappedSuperClass) {
                $className          = (string)$mappedSuperClass['name'];
                $result[$className] = $mappedSuperClass;
            }
        } else if (isset($xmlElement->embeddable)) {
            foreach ($xmlElement->embeddable as $embeddableElement) {
                $embeddableName          = (string)$embeddableElement['name'];
                $result[$embeddableName] = $embeddableElement;
            }
        }

        return $result;
    }

}