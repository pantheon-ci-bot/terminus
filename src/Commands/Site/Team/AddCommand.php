<?php

namespace Pantheon\Terminus\Commands\Site\Team;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Models\SiteUserMembership;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class AddCommand.
 *
 * @package Pantheon\Terminus\Commands\Site\Team
 */
class AddCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Adds a user to a site team.
     * Note: An invite will be sent if the email is not associated with a Pantheon account.
     *
     * @authorize
     *
     * @command site:team:add
     *
     * @param string $site_id Site name
     * @param string $member Email of user
     * @param string $role [developer|team_member] Role
     *
     * @usage <site> <user> Adds <user> as a team_member to <site>'s team.
     * @usage <site> <user> <role> Adds <user> as a <role> to <site>'s team.
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     * @throws \Exception
     */
    public function add($site_id, $member, $role = SiteUserMembership::ROLE_TEAM_MEMBER)
    {
        $site = $this->getSite($site_id);
        $team = $site->getUserMemberships();

        if ($role !== SiteUserMembership::ROLE_TEAM_MEMBER && !$site->getFeature('change_management')) {
            $role = SiteUserMembership::ROLE_TEAM_MEMBER;
            $this->log()->warning(
                'Site does not have change management enabled, defaulting to user role {role}.',
                compact('role')
            );
        }

        $workflow = $team->create($member, $role);
        $this->processWorkflow($workflow);
        $this->log()->notice($workflow->getMessage());
    }
}
