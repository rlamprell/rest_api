<?php
/*  This file is the main workhorse of the API.

    It processes requests and interacts with the database as appropriate.

*/


// inheritance class
// -- contains some useful functions shared across the Team and Player classes
class DbFunctions {

    private $conn;

    // the constructor only hold the db info
    public function __construct($db) {

        $this->conn = $db->getConnection();
    }


    // set all the parts of the class
    // -- example: Team - id, name, sport
    public function set($source) {
        if (is_object($source)) {

            $source = (array)$source;
        }
        foreach ($source as $key=>$value) {
            if (in_array($key, $this->parts)) {

                $this->$key = $value;
            }
            else {

                // error, attribute not found
                return FALSE;
               }
        }

        return TRUE;
    }


    // ensure no null values (used for store)
    public function validate() {
        foreach ($this->parts as $key) {
            if (is_null($this->$key)) {

                return FALSE;
            }
        }

        return TRUE;
    }


    // return this object (public properties only) as a JSON string
    // -- this isn't used for anything in the assignment
    public function __toString() {
        return json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    // Perform a read from the mysql database
    public function readQuery($id, $sort=null) {

        $query = "SELECT * FROM " . $this->table;

        // if an id has been provided then append to the query
        if (!is_null($id)) {

            $query = $query . " WHERE id = :id";
        }

        // if a sort has been provided then append to the query
        if (!is_null($sort)) {

            $query = $query . " ORDER BY " . $sort;
        }

        try {

            $sp = $this->conn->prepare($query);
            $sp->bindParam(':id', $id);
            $sp->execute();

            // db and status code of success
            return $sp;
        }
        catch (PDOException $err) {
            
            // 500 -- internal server error
            throw new Exception(500);
        }
    }
    

    // return the db connection
    public function getConnection() {

        return $this->conn;
    }
}



// Teams
class Team extends DbFunctions{

    // private and protected properties don't show up in JSON encoding
    protected $table = 'Teams';
    protected $parts = ['id','name','sport','average_age'];
    public $id, $name, $sport, $average_age, $_links;


    // related links
    public function setLinks() { 

        // link to the collection resource for all players of each team
        $this->_links[] = 
        [(object)  ['href'   => "/teams/$this->id",
                    'method' => 'GET', 'rel' => 'self'],
         (object)  ['href'   => "/teams/$this->id/players",
                    'method' => 'GET', 'rel' => 'collection'],
         (object)  ['href'   => "/teams/$this->id/players",
                    'method' => 'POST', 'rel' => 'add']
        ];
    }


    // GET -- read all the data
    // Put all returned info into an array
    public function readAll($db, $teamId) { 
        
        // Run a read query for the team(s)
        // -- second parameter is a field to sort by (only supports one)
        $sp = $this->readQuery($teamId, 'name');

        // get the class name 
        $className = self::class;

        // create a collection to be returned to the REST api
        $allTeams = [];
        foreach ($sp as $row) {

            // contruct new team based on info in row
            $t = new $className($db);

            // set all the public info from the db fields
            $valid = $t->set($row);
            if (!$valid) {

                return [null, 400];
            }

            // set all the links
            $t->setLinks();

            // push this single into the collection
            array_push($allTeams, $t);
        }

        // if empty give an error
        if (empty($allTeams)) {

            // Nothing found
            $stat = 404;
        }
        else {

            // found something
            $stat = 200;
        }

        // return that array along with the status
        return [$allTeams, $stat];
    }
}



// Player - redo
class Player extends DbFunctions {

    // private and protected properties don't show up in JSON encoding
    protected $table = 'Players';
    protected $parts = ['id','surname','forenames','nationality', 'date_of_birth', 'team_id'];
    public $id, $surname, $forenames, $nationality, $date_of_birth, $team_id, $_links;
    
    // need a team id to explore the players of the team
    public function __construct($db, $tid) {

        // invoke the DbFunctions constructor
        // https://www.tutorialchip.com/php/php-class-inheritance-constructor-you-should-know/
        parent::__construct($db);

        // set the team id
        $this->team_id = $tid;
    }


    // related links
    public function setLinks($playerId=null) { 

        // link to the collection resource for all players of each team/
        $this->_links[] = 
        [(object)  ['href'   => "/teams/$this->team_id/players/$playerId",
                    'method' => 'GET', 'rel' => 'self'],
         (object)  ['href'   => "/teams/$this->team_id/players/$playerId",
                    'method' => 'PUT', 'rel' => 'edit'],
         (object)  ['href'   => "/teams/$this->team_id/players/$playerId",
                    'method' => 'PATCH', 'rel' => 'edit'],
         (object)  ['href'   => "/teams/$this->team_id/players/$playerId",
                    'method' => 'DELETE', 'rel' => 'delete']
        ];
    }


    // POST
    // -- add a player to a team
    public function store($data) { 

        try {
            // set the all the objects 
            // -- throws error if invalid one param provided
            $this->set($data);

            $this->getConnection()->beginTransaction();

            // if no player id is provided then create one
            if(is_null($this->id)) {

                $query = "SELECT max(id) FROM " . $this->table;

                $stmt = $this->getConnection()->prepare($query);
                $stmt->execute();

                $this->id = $stmt->fetchColumn(0) + 1;
            }
           
            // check none of the inputs are null
            if(!$this->validate()) {

                // incomplete data 400 (bad request)
                return [null, 400];
            }

            // insert the the request into the db
            // if the id is null then use auto_increment in the db
            if (is_null($this->id)) {
                $query = "INSERT INTO Players VALUES (NULL, :surname, :forenames, :nationality, :date_of_birth, :team_id)";

                $stmt = $this->getConnection()->prepare($query);

                $stmt->bindParam(":surname",        $this->surname);
                $stmt->bindParam(":forenames",      $this->forenames);
                $stmt->bindParam(":nationality",    $this->nationality);
                $stmt->bindParam(":date_of_birth",  $this->date_of_birth);
                $stmt->bindParam(":team_id",        $this->team_id);

                $stmt->execute();
            }
            // else use the id provided
            else {

                $query = "INSERT INTO Players VALUES (:id, :surname, :forenames, :nationality, :date_of_birth, :team_id)";

                $stmt = $this->getConnection()->prepare($query);

                $stmt->bindParam(":id",             $this->id);
                $stmt->bindParam(":surname",        $this->surname);
                $stmt->bindParam(":forenames",      $this->forenames);
                $stmt->bindParam(":nationality",    $this->nationality);
                $stmt->bindParam(":date_of_birth",  $this->date_of_birth);
                $stmt->bindParam(":team_id",        $this->team_id);

                $stmt->execute();
            }

            // update the average ages of the team
            $query = "CALL update_avg_ages(:team_id)";

            $stmt = $this->getConnection()->prepare($query);
            $stmt->bindParam(":team_id", $this->team_id);
            $stmt->execute();

            // no errors encountered so commit
            $this->getConnection()->commit();

            $url = "http://student.csc.liv.ac.uk/~sgrlampr/v1/teams/" . $this->team_id . "/players/" . $this->id;

            // return the user id and the status
            return [$url, 201];
        } 
        catch (PDOException $err) {
            
            // Internal server error
            return [null, 400];
        }
    }

    // @override
    // Perform a read from the mysql database
    public function readQuery($team_id, $player_id=null) {

        $query = "SELECT * FROM " . $this->table . " WHERE team_id = :id";

        // add player_id to the query if it has been provided
        if (!is_null($player_id)) {

            $query = $query . " AND id = :pid";
        }

        try {
            $sp = $this->getConnection()->prepare($query);
            $sp->bindParam(':id',  $team_id);

            if (!is_null($player_id)) {

                $sp->bindParam(':pid', $player_id);
            }

            $sp->execute();
    
            // db and status code of success
            return $sp;
        }
        catch (PDOException $err) {
            
            return FALSE;
        }
    }

    
    // null playerid incase getting all
    public function readAll($db, $playerId=null) {

        // Run a read query for the team(s)
        $sp = $this->readQuery($this->team_id, $playerId);

        // get the class name 
        $className = self::class;

        // create a collection to be returned to the REST api
        $allPlayers = [];
        foreach ($sp as $row) {

            // contruct new team based on info in row
            $t = new $className($db, $this->team_id);

            // set all the public info from the db fields
            $valid = $t->set($row);
            if (!$valid) {

                return [null, 400];
            }

            // set all the links
            $t->setLinks($t->id);

            // push this single into the collection
            array_push($allPlayers, $t);
        }

        // if empty give an error
        if (empty($allPlayers)) {

            // Nothing found
            $stat = 404;
        }
        else {

            // found something
            $stat = 200;
        }

        // return that array along with the status
        return [$allPlayers, $stat];
    }


    // delete a player from a team 
    public function delete($db, $playerId) { 
        
        try {

            $this->getConnection()->beginTransaction();

            // check there is an entry in the db to delete
            // -- if not, end the function and return 404 (NOT FOUND)
            $exist = $this->readAll($db, $playerId)[1];
            if ($exist!=200) {

                return [null, 404];
            }

            // Delete the player from the db
            $query = "DELETE FROM Players WHERE id      = :playerId
                                          AND team_id   = :team_id";

            $stmt = $this->getConnection()->prepare($query);
            $stmt->bindParam(':playerId',   $playerId);
            $stmt->bindParam(':team_id',     $this->team_id);

            $stmt->execute();

            // update the average ages of the team
            $query = "CALL update_avg_ages(:team_id)";

            $stmt = $this->getConnection()->prepare($query);
            $stmt->bindParam(":team_id", $this->team_id);
            $stmt->execute();


            $this->getConnection()->commit();

            return [null, 204];
        }
        catch (PDOException $err) {
            
            $this->getConnection()->rollBack();
            // 500 -- internal server error
            return [null, 500];
        }
    }


    // update one or more fields in the player table
    public function update($db, $id, $data, $method) {
        
        try {    

            // check if there is an entry in the db to update, if not and the method is:
            // -- PATCH, report the entry does not exist (404 NOT FOUND)
            // -- PUT, attempt to run a POST query
            $exist = $this->readAll($db, $id)[1];
            if ($exist!=200 && $method=='PATCH') {

                return [null, 404];
            }
            if ($exist!=200 && $method=='PUT') {
                
                $this->id = $id;
                return $this->store($data);
            }

            $this->getConnection()->beginTransaction();

            // update each of the $data values (parameters provided by the user)   
            foreach ($data as $key=>$value){

                $query  =  "UPDATE Players
                            SET " . $key. " = :bind 
                            WHERE id = :id";


                $stmt = $this->getConnection()->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':bind', $value);
                $stmt->execute();
            }

            $this->getConnection()->commit();

            return [null, 200];
        }
        catch (PDOException $err) {

            $this->getConnection()->rollBack();
            // Bad request
            return [null, 400];
        }
    }
}


// API root HATEOAS links
class RootLinks {

    // Resources the api can access
    // -- only teams is available from the root and has no methods associated
    //    with it other than 'GET'
    // -- teams/$teamid/players is the next and will be displayed when returning
    //    any 'GET's from th8e /teams
    public function returnLinks() {

        $links[] = 
        [(object)  ['href'   => "/teams",   
                    'method' => 'GET', 'rel' => 'collection']];

        return $links;
    }
}