<?php

namespace App\Entity\Interfaces\Modules;

/**
 * This interface should only be applied on entities which are directly representing elements of given module
 *  which further means that for example module can consist:
 *   - Issue,
 *   - IssueContact,
 *   - IssueProgress
 *
 * However the main Entity in that case would be Issue as it is the single entity which relates with additional one
 * and the additional one are not allowed to live as separate beings
 *
 * Interface ModuleMainEntityInterface
 */
interface ModuleMainEntityInterface {

    public function getRelatedModuleName(): string;

}