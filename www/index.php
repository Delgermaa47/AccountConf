<?php
    define('ROOT',  dirname((dirname(__FILE__))));
    require_once ROOT."\inc\utils.php";
    require_once ROOT."\inc\db.php";


    $uri = rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/' );
    $uri = '/' . trim( str_replace( $uri, '', $_SERVER['REQUEST_URI'] ), '/' );
    $uri = urldecode( $uri );

    $get_requests = array( 
        'invoice-detail' => "/api/invoice-detail/(?'invno'\d+)",
    );

    $page_requests = array( 
        'home' => "/",
    );

    $post_requests = array(
        'invoice-detail' => "/api/invoice-detail/(?'invno'\d+)",
    );
    
    $request_method = $_SERVER['REQUEST_METHOD'];
    if (in_array($request_method, ['GET', 'POST'])) {

        if (strstr($uri, "api")) {
            $request_rules = $request_method === 'GET' ? $get_requests : $post_requests;
            require_once ROOT."\\inc\\components\\api_request.php";
            $req = new ApiList(); 
        }
        else {
            $request_rules = $page_requests;
            require_once ROOT."\\inc\\components\\page_request.php";
            $req = new PageRequest();
        }
        
        foreach ( $request_rules as $action_name => $rule ) {
            if ( preg_match( '~^'.$rule.'$~i', $uri, $params ) ) {
                $req->request_name = $action_name;
                $req->params = $params;
                $res = $req->request_res();
                if (strstr($uri, "api")) {
                    header('Access-Control-Allow-Origin: *');
                    header('Content-Type: application/json');
                    echo $res;
                    die();
                }
              
            }
        }

        http_response_code(404);
        console_log(
            '<div class="page404">
                <span>404</span>      
                <p>page not found</p>
            </div>
            '
        );

    }
    else {
        header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
        die();
    }
?>
