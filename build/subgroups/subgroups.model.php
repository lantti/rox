<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @author mahouni
     */
    /**
     * the model of the groups mvc
     *
     * @package Apps
     * @subpackage Subgroups
     */
     

class SubgroupsModel extends  RoxModelBase
{
    private $_subgroup_list = 0;
    
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Find and return one group, using id
     *
     * @param int $groupId
     * @return mixed false or a Group entity
     */    
    public function findGroup($groupId)
    {
        $group = $this->createEntity('Group',$groupId);
        if ($group->isLoaded()) {
            return $group;
        } else {
            return false;
        }
    }


    /**
     * Find all groups I am member of and which are not subgroups of a given group
     *
     * @param object $group - a group entity 
     * @access public
     * @return array() Returns an array
     */
    public function getMyGroups(Group $group)
    {
        $notsubgroups = array();
        if (!isset($_SESSION['IdMember'])) {
            return array();
        } else {
            $mygroups = $this->getGroupsForMember($_SESSION['IdMember']);
            $subgroups = $group->findSubgroups($group->getPKValue());
            foreach ($mygroups as $mygroup) {
                if (!in_array($mygroup, $subgroups)) {
                    $notsubgroups[] = $mygroup;
                }
                    
            }
                
            return $notsubgroups;
        }
    }



    /**
     * Find all groups $memberId is member of
     *
     * @access public
     * @return mixed Returns an array of Group entity objects or false if you're not logged in
     */
    public function getGroupsForMember($memberId)
    {
        $memberId = intval($memberId);
        if ($memberId == 0) {
            return false;
        }

        $member = $this->createEntity('Member')->findById($memberId);
        return $member->getGroups();

    }



    public function memberAddsSubgroup($groupId, $subgroupId, $memberId)
    {
        $group = $this->createEntity('Group', $groupId);
        $subgroup = $this->createEntity('Group', $subgroupId);
        $member = $this->createEntity('Member', $memberId);
        $add = $this->createEntity('Subgroup')->AddSubgroup($group,$subgroup,$member);
        return $add;

    }



    public function memberDeletesSubgroup($groupId, $subgroupId, $memberId)
    {
        $group = $this->createEntity('Group', $groupId);
        $subgroup = $this->createEntity('Group', $subgroupId);
        $member = $this->createEntity('Member', $memberId);
        $where_clause = "subgroup_id = '{$subgroupId}' AND group_id = '{$groupId}' AND deletedby IS NULL";
        $subgroupentry = $this->createEntity('Subgroup')->findByWhere($where_clause);
        if (!$subgroupentry) {
            return false;
        }
        $delete = $subgroupentry->deleteSubgroup($member);
        return $delete;

    }



    public function showSubgroupsLog($groupId, $offset = 0, $limit = 25)
    {
        $group = $this->createEntity('Group', $groupId);
        $Subgroupslog = $this->createEntity('Subgroup')->getSubgroupsLog($group, $offset = 0, $limit = 25);
        return $Subgroupslog;
    }

}


