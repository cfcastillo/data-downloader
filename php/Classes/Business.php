<?php
namespace CFiniello\DataDownloader;

require_once("autoload.php");
require_once(dirname(__DIR__) . "vendor/autoload.php");

use Ramsey\Uuid\Uuid;

/**
 * Class Business
 * @package CFiniello\DataDownloader
 */
class Business { //implements \JsonSerializable {
	use ValidateUuid;

	/**
	 * @var
	 */
	private $businessId;
	private $businessName;
	private $businessYelpUrl;
	private $businessYelpId;
	private $businessLat;
	private $businessLong;


	public function __construct($newBusinessId, $newBusinessName, $newBusinessYelpUrl, $newBusinessYelpId, $newBusinessLat, $newBusinessLong) {
		try {
			$this->setBusinessId($newBusinessId);
			$this->setBusinessName($newBusinessName);
			$this->setBusinessYelpUrl($newBusinessYelpUrl);
			$this->setBusinessYelpId($newBusinessYelpId);
			$this->setBusinessLat($newBusinessLat);
			$this->setBusinessLong($newBusinessLong);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
	}

	public function getBusinessId() : Uuid{
		return $this->businessId;
	}

	/**
	 * @param mixed $businessId
	 */
	public function setBusinessId($newBusinessId) {
		try {
			$uuid = self::validateUuid($newBusinessId);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
		$this->businessId = $uuid;
	}

	/**
	 * @return mixed
	 */
	public function getBusinessName() {
		return $this->businessName;
	}

	/**
	 * @param mixed $businessName
	 */
	public function setBusinessName($newBusinessName) {
		//sanitize the data.
		$newBusinessName = filter_var($newBusinessName, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newBusinessName) === true) {
			throw(new \InvalidArgumentException("Business name is empty or insecure"));
		}
		//Just truncate and issue warning.
		if(strlen($newBusinessName) > 128) {
			throw(new \RangeException("Business name is longer than 128 characters"));
		}

		$this->businessName = $newBusinessName;
	}

	/**
	 * @return mixed
	 */
	public function getBusinessYelpUrl() {
		return $this->businessYelpUrl;
	}

	/**
	 * @param mixed $businessYelpUrl
	 */
	public function setBusinessYelpUrl($newBusinessYelpUrl) {
		//TODO: ensure this is clean data - check for valid url.
		try {
			$newBusinessYelpUrl = filter_var($newBusinessYelpUrl, FILTER_VALIDATE_URL);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
				$exceptionType = get_class($exception);
				throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
		if(strlen($newBusinessYelpUrl) > 255) {
			throw(new \RangeException("Yelp url is longer than 255 characters"));
		}

		$this->businessYelpUrl = $newBusinessYelpUrl;
	}

	/**
	 * @return mixed
	 */
	public function getBusinessYelpId() {
		return $this->businessYelpId;
	}

	/**
	 * @param mixed $businessYelpId
	 */
	public function setBusinessYelpId($newBusinessYelpId) {
		//ensure this is clean data.

		if(strlen($newBusinessYelpId) > 32) {
			throw(new \RangeException("Yelp Id is longer than 32 characters."));
		}
		$this->businessYelpId = $newBusinessYelpId;
	}

	/**
	 * @return mixed
	 */
	public function getBusinessLat() {
		return $this->businessLat;
	}

	/**
	 * @param mixed $businessLat
	 */
	public function setBusinessLat($newBusinessLat) {
		//ensure this is decimal data type
		try {
			$newBusinessLat = filter_var($newBusinessLat, FILTER_VALIDATE_FLOAT);
		} catch (\TypeError $exception) {
			throw(new \TypeError("Business latitude value is an invalid data type"));
		}

		$this->businessLat = $newBusinessLat;
	}

	/**
	 * @return mixed
	 */
	public function getBusinessLong() {
		return $this->businessLong;
	}

	/**
	 * @param mixed $businessLong
	 */
	public function setBusinessLong($newBusinessLong) {
		//ensure this is decimal data type
		try {
			$newBusinessLong = filter_var($newBusinessLong, FILTER_VALIDATE_FLOAT);
		} catch (\TypeError $exception) {
			throw(new \TypeError("Business longitude value is an invalid data type"));
		}

		$this->businessLong = $newBusinessLong;
	}

	public function insert(\PDO $pdo) {
		// create query template
		$query = "INSERT INTO business(businessId, businessName, businessYelpUrl, businessYelpId, businessLat, businessLong) 
						VALUES(:businessId, :businessName, :businessYelpUrl, :businessYelpId, :businessLat, :businessLong)";
		$statement = $pdo->prepare($query);

		// bind the member variables to the place holders in the template
		$parameters = ["businessId" => $this->getBusinessId()->getBytes(),
			"businessName" => $this->businessName,
			"businessYelpUrl" => $this->businessYelpUrl,
			"businessYelpId" => $this->businessYelpId,
			"businessLat" => $this->businessLat,
			"businessLong" => $this->businessLong];
		$statement->execute($parameters);
	}

	public function getBusinessByBusinessId(\PDO $pdo, $businessId){
		// sanitize the id before searching
		try {
			$businessId = self::validateUuid($businessId);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}

		// create query template
		$query = "SELECT $businessId, businessName, businessYelpUrl, businessYelpId, businessLat, businessLong 
						FROM business 
						WHERE $businessId = :$businessId";
		$statement = $pdo->prepare($query);

		// bind the id to the place holder in the template
		$parameters = ["$businessId" => $this->getBusinessId()->getBytes()];
		$statement->execute($parameters);

		// grab the object from mySQL
		try {
			$business = null;
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			$row = $statement->fetch();
			if($row !== false) {
				$business = new Business($row["$businessId"], $row["businessName"], $row["businessYelpUrl"], $row["businessYelpId"],
					$row["businessLat"], $row["businessLong"]);
			}
		} catch(\Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}
		return($business);
	}
}