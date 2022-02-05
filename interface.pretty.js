// get the http request info from the input boxes and submit
var buildHTTPRequest = function () {

    // clear any previous request information
    clearRequestInfo();
    clearResponseInfo();

    // GET, DELETE, PUT, PATCH, POST
    // URL (example: http://localhost/v1/teams)
    // JSON body
    var method      = document.getElementById("method").value;
    var resource    = document.getElementById("resource").value;
    var jsonBody    = document.getElementById("request").value;

    processRequest(method, resource, jsonBody);
}


// clear any previous entries in the request fields
var clearRequestInfo = function () {

    // Print the request
    //  -- resource
    //  -- host
    document.getElementById("thisRequest").innerHTML        = null;
    document.getElementById("thisHost").innerHTML           = null;
    document.getElementById("reqContent").innerHTML         = null;
    document.getElementById("reqString").innerHTML          = null;
}

// clear any previous entries in the response fields
var clearResponseInfo = function() {

    // Print the response
    //  -- status
    //  -- content-type
    //  -- response body
    document.getElementById("httpStatus").innerHTML         = null;
    document.getElementById("contentType").innerHTML        = null;
    document.getElementById("responseBody").innerHTML       = null;
    document.getElementById("responseLocation").innerHTML   = null;
}


// process the http request and post the related related info on the html page
var processRequest = function(method, resource, jsonBody) {

    var base       = 'https://student.csc.liv.ac.uk/~sgrlampr/v1';
    var requestUrl = base + resource;

    console.log('starting ' + method + ' request');
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {

        // When done, if successful
        // -- 200 is if a get was successful
        // -- 201 is if a post was created 
        // -- 204 is successful delete with no return
        var successful = this.status == 200 || this.status ==201 || this.status == 204;
        if (this.readyState == 4 && successful) {
            
            // request
            //  -- resource
            //  -- host
            document.getElementById("thisRequest").innerHTML    = method + ' ' + resource + ' ' + this.getResponseHeader("Protocol");
            document.getElementById("thisHost").innerHTML       = 'Host: ' + location.host;

            // response
            //  -- status 
            document.getElementById("httpStatus").innerHTML     = this.getResponseHeader("Protocol") + ' ' + this.status +  ' ' +  this.statusText;

            if (method=='GET') {

                // response
                //  -- content-type
                //  -- response body
                document.getElementById("contentType").innerHTML    = "Content-Type: " + this.getResponseHeader("Content-Type");

                var prettyJSON = JSON.stringify(JSON.parse(this.response), null, 2);
                document.getElementById("responseBody").innerHTML = prettyJSON;
            }
            else if (method=='POST') {

                // request
                // -- request content
                // -- request string
                document.getElementById("reqContent").innerHTML     = "Content-Type: " + this.getResponseHeader("Content-Type");
                document.getElementById("reqString").innerHTML      = jsonBody;

                // response
                // -- location
                document.getElementById("responseLocation").innerHTML = "Location: " + this.getResponseHeader("Location");

            }
            else if (method=='PUT' || method=='PATCH') {

                // request
                // -- request content
                // -- request string
                document.getElementById("reqContent").innerHTML     = "Content-Type: " + this.getResponseHeader("Content-Type");

                // response
                document.getElementById("httpStatus").innerHTML     = this.getResponseHeader("Protocol") + ' ' + this.status +  ' ' +  this.statusText;

            }
            console.log(method + ' request completed');
        }
        // When done if not successful
        // 
        else if (this.readyState == 4 && !successful) {

            // request
            //  -- resource
            //  -- host
            document.getElementById("thisRequest").innerHTML    = method + ' ' + resource + ' ' + this.getResponseHeader("Protocol");
            document.getElementById("thisHost").innerHTML       = 'Host: ' + location.host;

            // response
            //  -- status 
            document.getElementById("httpStatus").innerHTML     = this.getResponseHeader("Protocol") + ' ' + this.status +  ' ' +  this.statusText;

            if (method=='POST') {

                // request
                // -- request content
                // -- request string
                document.getElementById("reqContent").innerHTML     = "Content-Type: " + this.getResponseHeader("Content-Type");
                document.getElementById("reqString").innerHTML      = jsonBody;
            }
            else if (method=='PUT' || method=='PATCH') {

                // request
                // -- request content
                document.getElementById("reqContent").innerHTML     = "Content-Type: " + this.getResponseHeader("Content-Type");

                // response
                // -- http status
                document.getElementById("httpStatus").innerHTML     = this.getResponseHeader("Protocol") + ' ' + this.status +  ' ' +  this.statusText;
            }
            console.log(method + ' request completed');
        }
        
    };

    // pass the request to REST.php to be processed, providing the:
    // -- method (GET, POST, ETC...)
    // -- the the URL/URI
    // -- any parameters required in jsonBody (example: {"name":"john"} )
    xhttp.open(method, requestUrl, true);
    xhttp.setRequestHeader("Content-type", "application/json");
    xhttp.send(jsonBody);
}


