<?php
namespace CFiniello\DataDownloader;

require_once("autoload.php");
require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Ramsey\Uuid\Uuid;

/**
 * Class Business
 * @package CFiniello\DataDownloader
 */
class Business { //implements \JsonSerializable {
	use ValidateUuid;

	/**
	 * State variables
	 * @var
	 */
	private $businessId;
	private $businessName;
	private $businessYelpUrl;
	private $businessYelpId;
	private $businessLat;
	private $businessLong;

	/**
	 * Business constructor.
	 * @param string $newBusinessId
	 * @param string $newBusinessName
	 * @param string $newBusinessYelpUrl
	 * @param string $newBusinessYelpId
	 * @param float $newBusinessLat
	 * @param float $newBusinessLong
	 */
	public function __construct(string $newBusinessId, string $newBusinessName, string $newBusinessYelpUrl, string $newBusinessYelpId, float $newBusinessLat, float $newBusinessLong) {
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

	/**
	 * @return Uuid
	 */
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
	 * @return string
	 */
	public function getBusinessName() : string {
		return $this->businessName;
	}

	/**
	 * @param string $newBusinessName
	 */
	public function setBusinessName(string $newBusinessName) {
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
	 * @return string
	 */
	public function getBusinessYelpUrl() : string {
		return $this->businessYelpUrl;
	}

	/**
	 * @param string $newBusinessYelpUrl
	 */
	public function setBusinessYelpUrl( string $newBusinessYelpUrl) {
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
	 * @return string
	 */
	public function getBusinessYelpId() : string {
		return $this->businessYelpId;
	}

	/**
	 * @param string $businessYelpId
	 */
	public function setBusinessYelpId(string $newBusinessYelpId) {
		//ensure this is clean data.

		if(strlen($newBusinessYelpId) > 32) {
			throw(new \RangeException("Yelp Id is longer than 32 characters."));
		}
		$this->businessYelpId = $newBusinessYelpId;
	}

	/**
	 * @return float
	 */
	public function getBusinessLat() : float {
		return $this->businessLat;
	}

	/**
	 * @param float $businessLat
	 */
	public function setBusinessLat(float $newBusinessLat) {
		//ensure this is decimal data type
		try {
			$newBusinessLat = filter_var($newBusinessLat, FILTER_VALIDATE_FLOAT);
		} catch (\TypeError $exception) {
			throw(new \TypeError("Business latitude value is an invalid data type"));
		}

		$this->businessLat = $newBusinessLat;
	}

	/**
	 * @return float
	 */
	public function getBusinessLong() : float {
		return $this->businessLong;
	}

	/**
	 * @param float $businessLong
	 */
	public function setBusinessLong(float $newBusinessLong) {
		//ensure this is decimal data type
		try {
			$newBusinessLong = filter_var($newBusinessLong, FILTER_VALIDATE_FLOAT);
		} catch (\TypeError $exception) {
			throw(new \TypeError("Business longitude value is an invalid data type"));
		}

		$this->businessLong = $newBusinessLong;
	}

	/**
	 * Insert a single record into the business table.
	 * @param \PDO $pdo
	 */
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

	/**
	 * Get a single record from the business table identified by businessId
	 * @param \PDO $pdo
	 * @param string $businessId
	 * @return Business|null
	 */
	public function getBusinessByBusinessId(\PDO $pdo, string $businessId){
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