<?php

namespace SimpleSAML\Module\drupalauth\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;
use SimpleSAML\Module\drupalauth\DrupalHelper;

/**
 * Event that is fired after SAML attributes are set
 */
class SetAttributesEvent extends Event
{
    public const EVENT_NAME = 'simplesamlphp_drupalauth_set_attributes';


    /**
     * Contstruct the event.
     *
     * @param \SimpleSAML\Module\drupalauth\DrupalHelper $drupalHelper
     * @param \Drupal\user\UserInterface $user
     * @param array $attributes
     */
    public function __construct(
        protected readonly DrupalHelper $drupalHelper,
        protected readonly UserInterface $user,
        protected readonly array $requestedAttributes,
        protected array $attributes
    ) {
    }

    /**
     * Get the drupal helper.
     */
    public function getDrupalHelper(): DrupalHelper
    {
        return $this->drupalHelper;
    }

    /**
     * Get the requested attributes.
     */
    public function getRequestedAttributes(): array
    {
        return $this->requestedAttributes;
    }

    /**
     * Get user who logged in.
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Get the attributes set.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes.
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
