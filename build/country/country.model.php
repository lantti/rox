<?php
/**
* Country model
* 
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class Country extends PAppModel {
	private $_dao;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getCountryInfo($countrycode) {
		$query = sprintf("SELECT `name`, `continent`
			FROM `geonames_countries`
			WHERE `iso_alpha2` = '%s'",
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}
    
	public function getRegionInfo($regioncode,$countrycode) {
		$query = sprintf("SELECT regions.name AS region, regions.id AS regionId, countries.isoalpha2 AS countryId
            FROM regions, cities, countries
            WHERE  cities.idregion = regions.id AND cities.IdCountry=countries.Id AND regions.country_code=countries.isoalpha2 AND regions.name = '%s'",
			$this->dao->escape($regioncode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}	
    
	public function getMembersOfCountry($countrycode) {
        $query = "SELECT username FROM members,cities,countries WHERE members.IdCity=cities.id AND cities.IdCountry=countries.id AND countries.isoalpha2='".$countrycode."'";
		$query2 = sprintf("SELECT `handle`
			FROM `user`
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `active` = '1' AND `geonames_cache`.`fk_countrycode` = '%s'
			ORDER BY `handle` ASC",
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		$members = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$members[] = $row->username;
		}
		return $members;
	}
    
	public function getMembersOfRegion($regioncode, $countrycode) {
        $query = "SELECT username FROM members,cities,regions,countries WHERE members.IdCity=cities.id AND cities.IdCountry=countries.id AND cities.idregion=regions.id AND regions.name='".$regioncode."' AND countries.isoalpha2='".$countrycode."'";
		$query2 = sprintf("SELECT `handle`
			FROM `user`
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `active` = '1' AND `geonames_cache`.`fk_countrycode` = '%s'
			ORDER BY `handle` ASC",
			$this->dao->escape($regioncode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		$members = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$members[] = $row->username;
		}
		return $members;
	}	
	/**
	* Returns a 3-Dimensional array of all countries
	* Format:
	*	[Continent]
	*		[Country-Code]
	*			[Name] Name of the Country
	*			[Number] Number of members living in this country
	*/
	public function getAllCountries() {
		$query = "SELECT `isoalpha2`, COUNT(`members`.`id`) AS `number`
			FROM `members`,`cities`,`countries`
			WHERE `Status`='Active' AND cities.IdCountry=countries.Id AND members.IdCity=cities.id
			GROUP BY `isoalpha2`";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Country list.');
		}
		$number = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$number[$row->isoalpha2] = $row->number;
		}
		
		$query = "SELECT `isoalpha2` AS `code`, `name`, `continent`
			FROM `countries`
			ORDER BY `continent` ASC, `name` ASC";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Country list.');
		}
		$countries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->continent][$row->code]['name'] = $row->name;
			if (isset($number[$row->code]) && $number[$row->code]) {
				$countries[$row->continent][$row->code]['number'] = $number[$row->code];
			} else {
				$countries[$row->continent][$row->code]['number'] = 0;
			}
		}
		
        return $countries;
	}

	public function getAllRegions($countrycode) {
		$query = "SELECT regions.name  AS region, regions.country_code AS country
FROM regions, cities, countries
WHERE  cities.idregion = regions.id AND cities.IdCountry=countries.Id AND regions.country_code='".$countrycode."' GROUP BY regions.id ORDER BY regions.name";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve region list.');
		}
		$regions = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$regions[] = $row->region;
		}
		
        return $regions;
	}    
	
}
?>
