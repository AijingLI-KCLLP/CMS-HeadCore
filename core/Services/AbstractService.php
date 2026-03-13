<?php

namespace Core\Services;

use Core\Repositories\AbstractRepository;

/**
 * Base class for all Services.
 *
 * Architecture rules:
 *  - Business logic lives here, not in controllers or repositories.
 *  - No direct PDO/DB access — always go through a Repository.
 *  - Controllers must stay thin: they call a Service method and return the Response.
 */
abstract class AbstractService
{
    public function __construct(protected AbstractRepository $repository) {}
}