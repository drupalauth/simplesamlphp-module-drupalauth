<?php

declare(strict_types=1);

namespace SimpleSAML\Module\drupalauth\Controller;

use SimpleSAML\HTTP\RunnableResponse;
use SimpleSAML\Module\drupalauth\Auth\Source\External;
use Symfony\Component\HttpFoundation\Request;

class ResumeController
{

  /**
   * Resume.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request The current request.
   *
   * @return \SimpleSAML\HTTP\RunnableResponse
   */
  public function resume(Request $request): \SimpleSAML\HTTP\RunnableResponse
  {
    /**
     * This page serves as the point where the user's authentication
     * process is resumed after the login page.
     *
     * It simply passes control back to the class.
     */
    return new \SimpleSAML\HTTP\RunnableResponse([External::class, 'resume'], [$request]);
  }
}