<?php
/*  This is file handles the API requests for the various METHODS:
        -- GET
        -- POST
        -- PUT
        -- PATCH
        -- DELETE.
    It can take in team and player ids along with addtional parameters for POST, PATCH and PUT.

    It will return:
        -- any relevant data
        -- header information
        -- and a status
*/

require_once('Database.php');
require_once('Model.php');


$db = new Database();


// error handling
set_exception_handler(function ($e) {

    $code = $e-> getCode() ?: 400;

    header ("Content-Type: application/json", NULL, $code);
    echo json_encode(["error" => $e-> getMessage()]);
    
    exit;
});


// Get info from the request
$method     = getenv('REQUEST_METHOD');
$protocol   = getenv('SERVER_PROTOCOL');
$resource   = explode('/', $_REQUEST['resource']);
$data       = json_decode(file_get_contents('php://input'),true);


// Handle request
// -- if the resource list is empty and the method is GET then provide a list of resources
if      ($resource[0]==NULL && $method=="GET") {

    $data = RootLinks::returnLinks();
}
// -- if the resource list is empty but the method is not GET then return an error
// -- there is a bug to do with the QSA rewrite rule which changes POST to GET when resource is blank
else if ($resource[0]==NULL && $method!="GET") {

    // 400 - Bad Request
    [$data, $status] = [null, 400];
}
// -- else use the $method to run the relevent function
else {
    // Request method
    switch($method) {
        case 'GET':
            [$data, $status]    = readData($db, $resource);
            break;

        case 'POST':
            [$location, $status]= createData($db, $resource, $data);
            // location header only required for POST -- 302 error could occur on other requests if the header 
            //                                           is placed with content-type and protocol headers below
            header("Location: " . $location);
            break;

        case 'PUT':
            [$data, $status]    = updateData($db, $resource, $data, $method);
            break;

        case 'PATCH':
            [$data, $status]    = updateData($db, $resource, $data, $method);
            break;

        case 'DELETE':
            [$data, $status]    = deleteData($db, $resource);
            break;

        default:
            // unsupported method
            [null, 405];
    }
}

// Response Headers
header("Content-Type: application/json", TRUE, $status);
header("Protocol: " . $protocol);

// Response body
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 



// GET 
// -- retrieve information on all teams
// -- retrieve information on all players of a specific team
// -- retrieve infromation on an existing player of a specific team
function readData($db, $resource) {

    // return information on all teams
    if ((count($resource)==1 || count($resource)==2) && 
        $resource[0] == "teams") {
        
        $t = new Team($db, $data);
        return $t->readAll($db, $resource[1]);
    } 
    // return information on all players of a specific team
    elseif ((count($resource) == 3 || (count($resource) == 4)) && 
            ($resource[0] == "teams") &&
            ($resource[2] == 'players')) {

        // resource[1] (teamid) is a number
        $isNumber = preg_match('/^[1-9][0-9]*$/',$resource[1]);
        if ($isNumber) {

            $p = new Player($db, $resource[1]);
            return $p->readAll($db, $resource[3]);
        } 
        else {

            // resource NOT FOUND - invalid team id
            return [null, 404];
        }  
    }
    // error number of attributes
    else {
        
        // resource NOT FOUND
        return [null, 404];
    } 
}


// POST 
// -- add a player to an existing team
function createData($db, $resource, $data) {

    // add a player to a specific team (create if not already created)
    if ((count($resource) == 3) && 
            ($resource[0] == "teams") &&
            ($resource[2] == 'players')) {

        // check the provided resource is numeric (teamd id)
        $teamIsNumber   = preg_match('/^[1-9][0-9]*$/', $resource[1]);
        
        if ($teamIsNumber) {
            
            $p = new Player($db, $resource[1]);
            return $p->store($data);
        } 
        else {

           // resource NOT FOUND
            return [null, 400];
        }  
    }
    // didn't fit any of the rules, through error
    else {

        // resource NOT FOUND
        return [null, 400];
    }
}

// DELETE
// -- delete an existing player from a team
function deleteData($db, $resource) {
    
    // return information on all players of a specific team
    if (count($resource) == 4 && 
             ($resource[0] == "teams") &&
             ($resource[2] == 'players')) {
        
        // check both resources are numeric inputs (team id & player id)
        $teamIsNumber   = preg_match('/^[1-9][0-9]*$/',$resource[1]);
        $playerIsNumber = preg_match('/^[1-9][0-9]*$/',$resource[3]);

        if ($teamIsNumber && $playerIsNumber) {
 
            $p = new Player($db, $resource[1]);
            return $p->delete($db, $resource[3]);
        } 
        else {

          // resource NOT FOUND
            return [null, 404];
        }  
    }
    // error number of attributes
    else {

        // resource NOT FOUND
        return [null, 404];
    } 
}

// UPDATE
// -- update information on an existing player of a team
function updateData($db, $resource, $data, $method) {

    
    // Update the information of a player on a specific team
    if ((count($resource) == 4) && 
        ($resource[0] == "teams") &&
        ($resource[2] == 'players')) {

        // check both resources are numeric inputs (team id & player id)
        $teamIsNumber   = preg_match('/^[1-9][0-9]*$/', $resource[1]);
        $playerIsNumber = preg_match('/^[1-9][0-9]*$/', $resource[3]);

        if ($teamIsNumber && $playerIsNumber) {
            
            $p = new Player($db, $resource[1]);
            return $p->update($db, $resource[3], $data, $method);
        } 
        else {

           // resource NOT FOUND
            return [null, 400];
        }
    }
    else {

          // resource NOT FOUND
            return [null, 400];
    }
}