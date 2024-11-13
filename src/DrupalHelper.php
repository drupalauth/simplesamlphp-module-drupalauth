<?php

namespace SimpleSAML\Module\drupalauth;

use Drupal\Core\DrupalKernel;
use SimpleSAML\Module\drupalauth\Event\SetAttributesEvent;
use Symfony\Component\HttpFoundation\Request;

class DrupalHelper
{
    private array $forbiddenAttributes = ['pass', 'status'];

    /**
     * Boot Drupal.
     *
     * @param string $drupalRoot Path to Drupal root.
     */
    public function bootDrupal(string $drupalRoot)
    {
        $autoloader = require_once $drupalRoot . '/autoload.php';
        $request = Request::createFromGlobals();
        $originalDir = getcwd();
        chdir($drupalRoot);
        $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod', true, $drupalRoot);
        $kernel->boot();
        $kernel->loadLegacyIncludes();
        chdir($originalDir);
    }

    /**
     * @param $drupaluser
     * @param $requested_attributes
     * @param $forbiddenAttributes
     * @return array
     */
    public function getAttributes($drupaluser, $requested_attributes): array
    {
        $attributes = [];
        $forbiddenAttributes = $this->forbiddenAttributes;

        if (empty($requested_attributes)) {
            $attributes = $this->getAllAttributes($drupaluser, $forbiddenAttributes);
        } else {
            foreach ($requested_attributes as $attribute) {
                $field_name = $attribute['field_name'];
                if ($drupaluser->hasField($field_name)) {
                    if (!in_array($field_name, $forbiddenAttributes, true)) {
                        $property_name = $this->getPropertyName($attribute);

                        $field = $drupaluser->{$field_name};

                        $field_properties = $field
                            ->getFieldDefinition()
                            ->getFieldStorageDefinition()
                            ->getPropertyDefinitions();
                        if (array_key_exists($property_name, $field_properties)) {
                            if (isset($attribute['field_index'])) {
                                if ($field->get($attribute['field_index'])) {
                                    $property_value = $field->get($attribute['field_index'])->{$property_name};
                                    if (!empty($property_value)) {
                                        $attribute_name = $this->getAttributeName($attribute);
                                        $attributes[$attribute_name][] = $property_value;
                                    }
                                }
                            } else {
                                $index = 0;
                                $count = $field->count();
                                while ($index < $count) {
                                    $property_value = $field->get($index)->{$property_name};
                                    if (!empty($property_value)) {
                                        $attribute_name = $this->getAttributeName($attribute);
                                        $attributes[$attribute_name][] = $property_value;
                                    }
                                    $index++;
                                }
                            }
                        }
                    }
                }
            }
        }
        $event = new SetAttributesEvent($this, $drupaluser, $requested_attributes, $attributes);
        $event_dispatcher = \Drupal::service('event_dispatcher');
        $event_dispatcher->dispatch($event, SetAttributesEvent::EVENT_NAME);
        $attributes = $event->getAttributes();
        return $attributes;
    }

    /**
     * @param $drupaluser
     * @param $forbiddenAttributes
     * @return array
     */
    protected function getAllAttributes($drupaluser, $forbiddenAttributes): array
    {
        $attributes = [];
        foreach ($drupaluser as $field_name => $field) {
            if (!in_array($field_name, $forbiddenAttributes, true)) {
                $count = $field->count();

                $field_properties = $field
                    ->getFieldDefinition()
                    ->getFieldStorageDefinition()
                    ->getPropertyDefinitions();
                foreach ($field_properties as $property_name => $property_definition) {
                    if (!$property_definition->isComputed() && !$property_definition->isInternal()) {
                        $index = 0;
                        while ($index < $count) {
                            $property_value = $field->get($index)->{$property_name};
                            if (!empty($property_value) && is_scalar($property_value)) {
                                $attributes["$field_name:$index:$property_name"][] = $property_value;
                            }
                            $index++;
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function getPropertyName($attribute_definition)
    {
        $property_name = 'value';
        if (!empty($attribute_definition['field_property'])) {
            $property_name = $attribute_definition['field_property'];
        }

        return $property_name;
    }

    public function getAttributeName($attribute_definition)
    {
        if (!empty($attribute_definition['attribute_name'])) {
            return $attribute_definition['attribute_name'];
        }

        $index = null;
        $field_name = $attribute_definition['field_name'];
        $property_name = $this->getPropertyName($attribute_definition);

        if (isset($attribute_definition['field_index'])) {
            $index = $attribute_definition['field_index'];
        }

        return isset($index) ? "$field_name:$index:$property_name" : "$field_name:$property_name";
    }
}
