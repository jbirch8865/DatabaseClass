<?php
namespace DatabaseLink;

Class SQLConnectionError extends Exception{}
Class SQLQueryError extends Exception{}
Class DuplicatePrimaryKeyRequest extends Exception{}

Class MySQLLink
{
	
	private $Database;
	private $LastInsertID;
	private $LastLogID;

	function __construct($Database)
	{
		try 
		{
			$this->EstablishDatabaseLink($Database);
		} catch (SQLConnectionError $e) 
		{
			throw new SQLConnectionError("Unreliable SQL object, couldn't properly connect to SQL host");
		}
	}
	
	private function EstablishDatabaseLink($Database)
	
	{
		If($Connection=mysqli_connect(getenv('MYSQL_HOSTNAME'), getenv('MYSQL_USERNAME'), getenv('MYSQL_PASSWORD'), $Database, getenv('MYSQL_LISTENING_PORT'))) 
		{	
			$this->Database = $Connection;
		} Else 
		{ 
			throw new SQLConnectionError("Error connecting to MySQL");
		}
	}
	
	function __set($name, $value)
	{
		try
		{
			$this->EstablishDatabaseLink($value);	
		} catch (SQLConnectionError $e)
		{
			
		}
	}
	//Dad, I just thought to myself what if I want to handle different kinds of SQL queries.  For example below you will notice that I store the LastInsertID, but this logic assumes every query is an INSERT.  So I was thinking, would I want to rely on mysql syntax to parse the request for the first word, which I believe SQL guarentees should be "SELECT, DELETE, INSERT, UPDATE"  I don't think there is any other option, but if by chance we could still have a other function that runs the query normally and still throws an exception if theres a query error, if not it just wouldn't be handled like the other 4 types.  If I do that how should I structure my ExectuteSQLQuery, case statement?
	function ExecuteSQLQuery( $Query, $Type = '10' )
	{
		Try
		{
			$Response = $this->QuerySQL($Query);
			$this->LastInsertID = mysqli_insert_id($this->Database);
			$this->AddToSyslog($Query, mysqli_error($this->Database), $Type);		
			return $Response;
		} catch (SQLQueryError $e)
		{
			throw new SQLQueryError("MySQL rejected this query - ".$Query);
		} catch (DuplicatePrimaryKeyRequest $e)
		{
			throw new DuplicatePrimaryKeyRequest("You are trying to create a duplicate entry for the primary key in the DB");
		}
	}
	
	private function QuerySQL($Query)
	{
		if(!$Response = mysqli_query($this->Database, $Query))
		{
//			echo mysqli_error($this->Database);
			if(mysqli_errno($this->Database) == '1062')
			{
				throw new DuplicatePrimaryKeyRequest("You are trying to create a duplicate entry for the primary key in the DB");
			}else
			{
				throw new SQLQueryError("SQL Server returned an error");
			}
		}else
		{
			return $Response;
		}
	}
	
	function AddToSyslog( $Query, $Response = "", $Type = '3' )
	{
		Try 
		{
			$this->QuerySQL("INSERT INTO syslog SET description = '$Query', Response = '$Response', Type = '$Type'");
			$this->LastLogID = mysqli_insert_id($this->Database);
		} catch (SQLQueryError $e)
		{

		}
	}

	function GetCurrentLink()
	{
		return $this->Database;
	}

	function GetLastInsertID()
	{
		return $this->LastInsertID;
	}

	function GetLastLogID()
	{
		return $this->LastLogID;
	}
}

Class Incident_Tickets_DB_Link 
{
	private $DBLink;
	public function __construct()
	{
		try
		{
			$this->SetDBLink();
		} catch (Exception $e)
		{
			throw new SQLConnectionError("There was an error connecting to the SQL DB");
		}
	}
	private function SetDBLink()
	{
		global $Incident_Tickets_Link;
		$this->DBLink = $Incident_Tickets_Link;
	}
	function GetDBLink()
	{
		return $this->DBLink;
	}	
}

Class Accounts_DB_Link 
{
	private $DBLink;
	public function __construct()
	{
		try
		{
			$this->SetDBLink();
		} catch (Exception $e)
		{
			throw new SQLConnectionError("There was an error connecting to the SQL DB");
		}
	}
	private function SetDBLink()
	{
		global $Accounts_Link;
		$this->DBLink = $Accounts_Link;
	}
	function GetDBLink()
	{
		return $this->DBLink;
	}	
}

Class Other_User_DB_Link 
{
	private $DBLink;
	public function __construct()
	{
		try
		{
			$this->SetDBLink();
		} catch (Exception $e)
		{
			throw new SQLConnectionError("There was an error connecting to the SQL DB");
		}
	}
	private function SetDBLink()
	{
		global $Other_User_Link;
		$this->DBLink = $Other_User_Link;
	}
	function GetDBLink()
	{
		return $this->DBLink;
	}
}

Class Feedback_Logs_DB_Link 
{
	private $DBLink;
	public function __construct()
	{
		try
		{
			$this->SetDBLink();
		} catch (Exception $e)
		{
			throw new SQLConnectionError("There was an error connecting to the SQL DB");
		}
	}
	private function SetDBLink()
	{
		global $Feedback_Logs_Link;
		$this->DBLink = $Feedback_Logs_Link;
	}
	function GetDBLink()
	{
		return $this->DBLink;
	}
}

Class DeveloperTools_DB_Link 
{
	private $DBLink;
	public function __construct()
	{
		try
		{
			$this->SetDBLink();
		} catch (Exception $e)
		{
			throw new SQLConnectionError("There was an error connecting to the SQL DB");
		}
	}
	private function SetDBLink()
	{
		global $DeveloperTools_Link;
		$this->DBLink = $DeveloperTools_Link;
	}
	function GetDBLink()
	{
		return $this->DBLink;
	}
	
}

////Due to the issues of constantly needing $User = new User($User_ID) or $Ticket = new Ticket($Ticket_ID) I was rapidly using up all the avaiable SQL thread connections.  So after researching online I decided to store all the necessary Database links as global variables and then build classes that load those global variables to local DBLink variables. Then I have a public method called GetDBLink in each class.  So instead of Exctends MySQLLink we will instead extend the instantion of the link we want the naming convention will be NameOfDatabase_DB_Link

global $Incident_Tickets_Link;
global $Accounts_Link;
global $Other_User_Link;
global $Feedback_Logs_Link;
global $DeveloperTools_Link;
$Incident_Tickets_Link = new MySQLLink('Incident_Tickets');
$Accounts_Link = new MySQLLink('Accounts');
$Other_User_Link = new MySQLLink('Accounts');
$Feedback_Logs_Link = new MySQLLink('Feedback/Logs');
$DeveloperTools_Link = new MySQLLink('DeveloperTools');
?>
