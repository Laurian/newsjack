<?php
###
# Info:
#  Last Updated 2012
#  Daniel Schultz
#
# Comments:
#  This class is the model for a remix object
###

include_once("Remix.php");

class Remix {
	
	# Constants
	// Initialization Types
	const INIT_EMPTY = -1;
	const INIT_DEFAULT = 0;
	
	
	# Instance Variables
	private $itemID; // int
	private $originalDOM; // string
	private $originalURL; // string
	private $remixDOM; // string
	private $remixURL; // string
	private $dateCreated; // timestamp
	
	
	public function __construct($itemID = Remix::INIT_EMPTY) {
		$dataArrays = static::gatherData((int)$itemID);
		$this->load($dataArrays[0]);
	}
	
	# FactoryObject Methods
	protected static function gatherData($objectString) {
		$dataArrays = array();
		
		// Load an empty object
		if($objectString === Remix::INIT_EMPTY) {
			$dataArray = array();
			$dataArray['itemID'] = 0;
			$dataArray['originalDOM'] = "";
			$dataArray['originalURL'] = "";
			$dataArray['remixDOM'] = "";
			$dataArray['remixURL'] = "";
			$dataArray['dateCreated'] = 0;
			$dataArrays[] = $dataArray;
			return $dataArrays;
		}
		
		// Load a default object
		if($objectString === Remix::INIT_DEFAULT) {
			$dataArray = array();
			$dataArray['itemID'] = 0;
			$dataArray['originalDOM'] = "";
			$dataArray['originalURL'] = "";
			$dataArray['remixDOM'] = "";
			$dataArray['remixURL'] = "";
			$dataArray['dateCreated'] = 0;
			$dataArrays[] = $dataArray;
			return $dataArrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$queryString = "SELECT remixes.id AS itemID,
							   remixes.original_dom AS originalDOM,
							   remixes.original_url AS originalURL,
							   remixes.remix_dom AS remixDOM,
							   remixes.remix_url AS remixURL,
							   unix_timestamp(remixes.date_created) AS dateCreated
						  FROM remixes
						 WHERE remixes.id IN (".$objectString.")";
		
		$result = $mysqli->query($queryString)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$dataArray = array();
			$dataArray['itemID'] = $resultArray['itemID'];
			$dataArray['originalDOM'] = $resultArray['originalDOM'];
			$dataArray['originalURL'] = $resultArray['originalURL'];
			$dataArray['remixDOM'] = $resultArray['remixDOM'];
			$dataArray['remixURL'] = $resultArray['remixURL'];
			$dataArray['dateCreated'] = $resultArray['dateCreated'];
			$dataArrays[] = $dataArray;
		}
		
		$result->free();
		return $dataArrays;
	}
	
	public function load($dataArray) {
		$this->itemID = isset($dataArray["itemID"])?$dataArray["itemID"]:0;
		$this->originalDOM = isset($dataArray["originalDOM"])?$dataArray["originalDOM"]:"";
		$this->originalURL = isset($dataArray["originalURL"])?$dataArray["originalURL"]:"";
		$this->remixDOM = isset($dataArray["remixDOM"])?$dataArray["remixDOM"]:"";
		$this->remixURL = isset($dataArray["remixURL"])?$dataArray["remixURL"]:"";
		$this->dateCreated = isset($dataArray["dateCreated"])?$dataArray["dateCreated"]:0;
	}
	
	
	# Data Methods
	public function validate() {
		return true;
	}
	
	public function save() {
		if(!$this->validate()) return;
		
		$mysqli = DBConn::connect();
		
		if($this->isUpdate()) {
			// Update an existing record
			echo("TEST");
			$queryString = "UPDATE remixes
							   SET remixes.original_dom = ".DBConn::clean($this->getOriginalDOM()).",
								   remixes.original_url = ".DBConn::clean($this->getOriginalURL()).",
								   remixes.remix_dom = ".DBConn::clean($this->getRemixDOM()).",
								   remixes.remix_url = ".DBConn::clean($this->getRemixURL())."
							 WHERE remixes.id = ".DBConn::clean($this->getItemID());
							
			echo($queryString);
			
			$mysqli->query($queryString);
		} else {
			// Create a new record
			$queryString = "INSERT INTO remixes
								   (remixes.id,
									remixes.original_dom,
									remixes.original_url,
									remixes.remix_dom,
									remixes.remix_url,
									remixes.date_created)
							VALUES (0,
									".DBConn::clean($this->getOriginalDOM()).",
									".DBConn::clean($this->getOriginalURL()).",
									".DBConn::clean($this->getRemixDOM()).",
									".DBConn::clean($this->getRemixURL()).",
									NOW())";
			
			$mysqli->query($queryString);
			$this->setItemID($mysqli->insert_id);
		}
		
		// Parent Operations
		return true;
	}
	
	public function delete() {
		parent::delete();
		$mysqli = DBConn::connect();
		
		// Delete this record
		$queryString = "DELETE FROM remixes
						 WHERE remixes.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($queryString);
		
	}
	
	
	# Getters
	public function getOriginalDOM() { return $this->originalDOM; }
	
	public function getOriginalURL() { return $this->originalURL; }
	
	public function getRemixDOM() { return $this->remixDOM; }
	
	public function getRemixURL() { return $this->remixURL; }
	
	public function getDateCreated() { return $this->dateCreated; }
	
	public function getItemID() { return $this->itemID; }
	
	
	# Setters
	public function setOriginalDOM($str) { $this->originalDOM = $str; }
	
	public function setOriginalURL($str) { $this->originalURL = $str; }
	
	public function setRemixDOM($str) { $this->remixDOM = $str; }
	
	public function setRemixURL($str) { $this->remixURL = $str; }
	
	private function setItemID($int) { $this->itemID = $int; }
	
	
	# Core Methods
	public final function isUpdate() { return ($this->getItemID() > 0); }
}
?>